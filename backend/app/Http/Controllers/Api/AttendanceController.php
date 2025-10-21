<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\LogAktivitasAbsensi;
use App\Models\JamKerja;
use App\Models\TempatKerja;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class AttendanceController extends Controller
{
    /**
     * Check in
     */
    public function checkIn(Request $request): JsonResponse
    {
        $user = $request->user();
        $today = Carbon::today();

        // Check if already checked in today
        $existingAttendance = Absensi::where('id_user', $user->id)
            ->whereDate('tanggal', $today)
            ->first();

        if ($existingAttendance && $existingAttendance->check_in) {
            return response()->json([
                'success' => false,
                'message' => 'You have already checked in today',
            ], 400);
        }

        // Get jam kerja for today
        $dayName = strtolower($today->format('l')); // Monday, Tuesday, etc.
        $dayMap = [
            'monday' => 'senin',
            'tuesday' => 'selasa',
            'wednesday' => 'rabu',
            'thursday' => 'kamis',
            'friday' => 'jumat',
            'saturday' => 'sabtu',
            'sunday' => 'minggu',
        ];
        $dayNameId = $dayMap[$dayName];

        // Get user's job title from profile pekerjaan
        $profilePekerjaan = $user->profilePekerjaan()->first();
        
        // Get user's tempat kerja
        $tempatKerja = null;
        $latitudeIn = $request->input('latitude_in');
        $longitudeIn = $request->input('longitude_in');
        
        if ($profilePekerjaan && $profilePekerjaan->id_tempat_kerja) {
            $tempatKerja = TempatKerja::find($profilePekerjaan->id_tempat_kerja);
            
            // Use tempat kerja coordinates if not provided
            if (!$latitudeIn && !$longitudeIn && $tempatKerja) {
                $latitudeIn = $tempatKerja->latitude;
                $longitudeIn = $tempatKerja->longitude;
            }
        }
        
        // Determine status based on jam kerja
        $status = 'hadir';
        $keterangan = $request->input('keterangan', null);
        
        if ($profilePekerjaan && $profilePekerjaan->id_jabatan) {
            $jamKerja = JamKerja::where('id_jabatan', $profilePekerjaan->id_jabatan)
                ->where('hari', $dayNameId)
                ->first();
            
            if ($jamKerja) {
                if ($jamKerja->is_libur) {
                    $status = 'alpa';
                    $keterangan = 'Hari libur';
                } elseif ($jamKerja->jam_masuk) {
                    $jamMasukKerja = Carbon::parse($jamKerja->jam_masuk);
                    $jamCheckIn = Carbon::now();
                    
                    // Check if terlambat (check in time is after jam masuk + 15 minutes)
                    $jamMasukToleransi = $jamMasukKerja->copy()->addMinutes(15);
                    
                    if ($jamCheckIn->gt($jamMasukToleransi)) {
                        $status = 'terlambat';
                        $keterangan = $keterangan ?: 'Terlambat ' . $jamCheckIn->diffForHumans($jamMasukKerja);
                    }
                }
            }
        }
        
        // Validate location if tempat kerja exists
        if ($tempatKerja && $latitudeIn && $longitudeIn) {
            $distance = $this->calculateDistance(
                $latitudeIn,
                $longitudeIn,
                $tempatKerja->latitude,
                $tempatKerja->longitude
            );
            
            // Define allowed radius (in meters) - bisa disesuaikan
            $allowedRadius = 200; // 200 meter radius
            
            // If distance more than allowed radius, return error
            if ($distance > $allowedRadius) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda berada di luar area kerja yang diizinkan',
                    'error' => [
                        'code' => 'LOCATION_OUT_OF_RANGE',
                        'details' => [
                            'current_distance' => round($distance),
                            'allowed_radius' => $allowedRadius,
                            'workplace_name' => $tempatKerja->nama_tempat,
                            'workplace_coordinates' => [
                                'latitude' => $tempatKerja->latitude,
                                'longitude' => $tempatKerja->longitude
                            ],
                            'current_coordinates' => [
                                'latitude' => $latitudeIn,
                                'longitude' => $longitudeIn
                            ]
                        ]
                    ]
                ], 400);
            }
        }

        // Handle file upload
        $filePendukung = null;
        if ($request->hasFile('file_pendukung')) {
            $file = $request->file('file_pendukung');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('public/absensi', $fileName);
            $filePendukung = $fileName;
        }

        $attendance = Absensi::updateOrCreate(
            [
                'id_user' => $user->id,
                'tanggal' => $today,
            ],
            [
                'check_in' => Carbon::now(),
                'latitude_in' => $latitudeIn,
                'longitude_in' => $longitudeIn,
                'status' => $status,
                'keterangan' => $keterangan,
                'file_pendukung' => $filePendukung,
            ]
        );

        // Log activity
        LogAktivitasAbsensi::create([
            'id_user' => $user->id,
            'id_absensi' => $attendance->id,
            'aksi' => 'check_in',
            'data_baru' => json_encode([
                'check_in' => $attendance->check_in,
                'status' => $attendance->status,
                'keterangan' => $attendance->keterangan,
            ]),
        ]);

        // Load relationships
        $attendance->load([
            'user.profilePekerjaan.departemen',
            'user.profilePekerjaan.tempatKerja',
            'user.profilePekerjaan.jabatan'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Check in successful',
            'data' => [
                'id' => $attendance->id,
                'status' => $attendance->status,
                'id_user' => $attendance->id_user,
                'tanggal' => $attendance->tanggal,
                'check_in' => $attendance->check_in,
                'latitude_in' => $attendance->latitude_in,
                'longitude_in' => $attendance->longitude_in,
                'keterangan' => $attendance->keterangan,
                'file_pendukung' => $attendance->file_pendukung,
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'jabatan' => $user->profilePekerjaan?->jabatan?->nama ?? null,
                    'departemen' => $user->profilePekerjaan?->departemen?->nama ?? null,
                    'tempat_kerja' => $user->profilePekerjaan?->tempatKerja?->nama_tempat ?? null,
                ],
                'jam_kerja' => $jamKerja ? [
                    'jam_masuk' => $jamKerja->jam_masuk,
                    'jam_pulang' => $jamKerja->jam_pulang,
                    'is_libur' => $jamKerja->is_libur,
                ] : null,
                'created_at' => $attendance->created_at,
                'updated_at' => $attendance->updated_at,
            ],
        ]);
    }

    /**
     * Check out
     */
    public function checkOut(Request $request): JsonResponse
    {
        $user = $request->user();
        $today = Carbon::today();

        $attendance = Absensi::where('id_user', $user->id)
            ->whereDate('tanggal', $today)
            ->first();

        if (!$attendance) {
            return response()->json([
                'success' => false,
                'message' => 'You must check in first',
            ], 400);
        }

        if ($attendance->check_out) {
            return response()->json([
                'success' => false,
                'message' => 'You have already checked out today',
            ], 400);
        }

        // Get user's tempat kerja
        $profilePekerjaan = $user->profilePekerjaan()->first();
        $tempatKerja = null;
        $latitudeOut = $request->input('latitude_out');
        $longitudeOut = $request->input('longitude_out');
        
        if ($profilePekerjaan && $profilePekerjaan->id_tempat_kerja) {
            $tempatKerja = TempatKerja::find($profilePekerjaan->id_tempat_kerja);
            
            // Use tempat kerja coordinates if not provided
            if (!$latitudeOut && !$longitudeOut && $tempatKerja) {
                $latitudeOut = $tempatKerja->latitude;
                $longitudeOut = $tempatKerja->longitude;
            }
        }

        // Validate location for check-out if tempat kerja exists
        if ($tempatKerja && $latitudeOut && $longitudeOut) {
            $distance = $this->calculateDistance(
                $latitudeOut,
                $longitudeOut,
                $tempatKerja->latitude,
                $tempatKerja->longitude
            );
            
            // Define allowed radius (in meters) - bisa disesuaikan
            $allowedRadius = 500; // 200 meter radius
            
            // If distance more than allowed radius, return error
            if ($distance > $allowedRadius) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda berada di luar area kerja yang diizinkan untuk check-out',
                    'error' => [
                        'code' => 'LOCATION_OUT_OF_RANGE',
                        'details' => [
                            'current_distance' => round($distance),
                            'allowed_radius' => $allowedRadius,
                            'workplace_name' => $tempatKerja->nama_tempat,
                            'workplace_coordinates' => [
                                'latitude' => $tempatKerja->latitude,
                                'longitude' => $tempatKerja->longitude
                            ],
                            'current_coordinates' => [
                                'latitude' => $latitudeOut,
                                'longitude' => $longitudeOut
                            ]
                        ]
                    ]
                ], 400);
            }
        }

        // Update keterangan if provided
        $keterangan = $request->input('keterangan', $attendance->keterangan);

        $attendance->update([
            'check_out' => Carbon::now(),
            'latitude_out' => $latitudeOut,
            'longitude_out' => $longitudeOut,
            'keterangan' => $keterangan,
        ]);

        // Log activity
        LogAktivitasAbsensi::create([
            'id_user' => $user->id,
            'id_absensi' => $attendance->id,
            'aksi' => 'check_out',
            'data_baru' => json_encode([
                'check_out' => $attendance->check_out,
                'keterangan' => $attendance->keterangan,
            ]),
        ]);

        // Load relationships
        $attendance->load([
            'user.profilePekerjaan.departemen',
            'user.profilePekerjaan.tempatKerja',
            'user.profilePekerjaan.jabatan'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Check out successful',
            'data' => [
                'id' => $attendance->id,
                'status' => $attendance->status,
                'id_user' => $attendance->id_user,
                'tanggal' => $attendance->tanggal,
                'check_in' => $attendance->check_in,
                'check_out' => $attendance->check_out,
                'latitude_in' => $attendance->latitude_in,
                'longitude_in' => $attendance->longitude_in,
                'latitude_out' => $attendance->latitude_out,
                'longitude_out' => $attendance->longitude_out,
                'keterangan' => $attendance->keterangan,
                'file_pendukung' => $attendance->file_pendukung,
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'jabatan' => $user->profilePekerjaan?->jabatan?->nama ?? null,
                    'departemen' => $user->profilePekerjaan?->departemen?->nama ?? null,
                    'tempat_kerja' => $user->profilePekerjaan?->tempatKerja?->nama_tempat ?? null,
                ],
                'created_at' => $attendance->created_at,
                'updated_at' => $attendance->updated_at,
            ],
        ]);
    }

    /**
     * Get today's attendance
     */
    public function getToday(Request $request): JsonResponse
    {
        $user = $request->user();
        $today = Carbon::today();

        $attendance = Absensi::where('id_user', $user->id)
            ->whereDate('tanggal', $today)
            ->first();

        // Load relationships if attendance exists
        if ($attendance) {
            $attendance->load([
                'user.profilePekerjaan.departemen',
                'user.profilePekerjaan.tempatKerja',
                'user.profilePekerjaan.jabatan'
            ]);
        }

        // Get jam kerja for today
        $profilePekerjaan = $user->profilePekerjaan()->first();
        $jamKerja = null;
        
        if ($profilePekerjaan && $profilePekerjaan->id_jabatan) {
            $dayName = strtolower($today->format('l'));
            $dayMap = [
                'monday' => 'senin',
                'tuesday' => 'selasa',
                'wednesday' => 'rabu',
                'thursday' => 'kamis',
                'friday' => 'jumat',
                'saturday' => 'sabtu',
                'sunday' => 'minggu',
            ];
            $dayNameId = $dayMap[$dayName];
            
            $jamKerja = JamKerja::where('id_jabatan', $profilePekerjaan->id_jabatan)
                ->where('hari', $dayNameId)
                ->first();
        }

        return response()->json([
            'success' => true,
            'message' => 'Today\'s attendance retrieved successfully',
            'data' => [
                'has_checked_in' => $attendance && $attendance->check_in ? true : false,
                'has_checked_out' => $attendance && $attendance->check_out ? true : false,
                'check_in_time' => $attendance && $attendance->check_in ? $attendance->check_in : null,
                'check_out_time' => $attendance && $attendance->check_out ? $attendance->check_out : null,
                'attendance' => $attendance,
                'jam_kerja' => $jamKerja ? [
                    'jam_masuk' => $jamKerja->jam_masuk,
                    'jam_pulang' => $jamKerja->jam_pulang,
                    'is_libur' => $jamKerja->is_libur,
                ] : null,
                'user_info' => [
                    'jabatan' => $profilePekerjaan?->jabatan?->nama ?? null,
                    'departemen' => $profilePekerjaan?->departemen?->nama ?? null,
                    'tempat_kerja' => $profilePekerjaan?->tempatKerja?->nama_tempat ?? null,
                    'tempat_kerja_latitude' => $profilePekerjaan?->tempatKerja?->latitude ?? null,
                    'tempat_kerja_longitude' => $profilePekerjaan?->tempatKerja?->longitude ?? null,
                ],
            ],
        ]);
    }

    /**
     * Get attendance history
     */
    public function getHistory(Request $request): JsonResponse
    {
        $user = $request->user();
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth());
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth());

        $attendance = Absensi::where('id_user', $user->id)
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->with([
                'user.profilePekerjaan.departemen',
                'user.profilePekerjaan.tempatKerja',
                'user.profilePekerjaan.jabatan'
            ])
            ->orderBy('tanggal', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Attendance history retrieved successfully',
            'data' => $attendance,
        ]);
    }

    /**
     * Calculate distance between two GPS coordinates using Haversine formula
     * Returns distance in meters
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2): float
    {
        $earthRadius = 6371000; // Earth radius in meters

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadius * $c;

        return $distance;
    }
}

