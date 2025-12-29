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
use Illuminate\Support\Facades\DB;

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
     * Get attendance history for a specific user (for HRD/Admin)
     */
    public function getUserHistory(Request $request, $userId): JsonResponse
    {
        $authUser = $request->user();
        
        // Check if user has permission to view other users' attendance
        $isAdmin = $authUser->hasAnyRole(['superadmin']);
        $isHRD = $authUser->hasAnyRole(['kepala hrd', 'staff hrd', 'direktur pendidikan']);
        $isKepalaSekolah = $authUser->hasAnyRole(['kepala sekolah']);
        $isKepalaDepartemen = $authUser->hasAnyRole(['kepala departemen']);
        
        // Allow users to view their own attendance
        if ($authUser->id == $userId) {
            // User viewing their own data - always allowed
        }
        // Admin and HRD have full access
        else if ($isAdmin || $isHRD) {
            // Full access - no additional checks needed
        }
        // Kepala Sekolah can only view tenaga pendidik from same workplace
        else if ($isKepalaSekolah) {
            // Get target user with their profile
            $targetUser = \App\Models\User::with(['profilePekerjaan.jabatan', 'profilePekerjaan.tempatKerja'])
                ->find($userId);
            
            if (!$targetUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found',
                ], 404);
            }
            
            // Get kepala sekolah's workplace
            $authUserWorkplace = $authUser->profilePekerjaan?->id_tempat_kerja;
            $targetUserWorkplace = $targetUser->profilePekerjaan?->id_tempat_kerja;
            
            // EXCLUSION: Cannot access superadmin and kepala yayasan attendance
            if ($targetUser->hasAnyRole(['superadmin', 'kepala yayasan'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to view this user\'s attendance.',
                ], 403);
            }
            
            // Check if target user is tenaga pendidik
            $isTenagaPendidik = $targetUser->hasRole('tenaga pendidik');
            
            // Check if same workplace
            $isSameWorkplace = $authUserWorkplace && $targetUserWorkplace && ($authUserWorkplace == $targetUserWorkplace);
            
            if (!$isTenagaPendidik || !$isSameWorkplace) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to view this user\'s attendance. Kepala Sekolah can only view Tenaga Pendidik from the same workplace.',
                ], 403);
            }
        }
        // Kepala Departemen can only view staff from same department
        else if ($isKepalaDepartemen) {
            // Get target user with their profile
            $targetUser = \App\Models\User::with(['profilePekerjaan.departemen'])
                ->find($userId);
            
            if (!$targetUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found',
                ], 404);
            }
            
            // EXCLUSION: Cannot access superadmin and kepala yayasan attendance
            if ($targetUser->hasAnyRole(['superadmin', 'kepala yayasan'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to view this user\'s attendance.',
                ], 403);
            }
            
            // Get kepala departemen's department
            $authUserDepartment = $authUser->profilePekerjaan?->id_departemen;
            $targetUserDepartment = $targetUser->profilePekerjaan?->id_departemen;
            
            // Check if same department
            $isSameDepartment = $authUserDepartment && $targetUserDepartment && ($authUserDepartment == $targetUserDepartment);
            
            if (!$isSameDepartment) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to view this user\'s attendance. Kepala Departemen can only view staff from the same department.',
                ], 403);
            }
        }
        else {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to view this user\'s attendance',
            ], 403);
        }

        // Build query
        $query = Absensi::where('id_user', $userId);
        
        // Apply date filter only if provided
        if ($request->has('start_date') && $request->has('end_date')) {
            $startDate = $request->get('start_date');
            $endDate = $request->get('end_date');
            $query->whereBetween('tanggal', [$startDate, $endDate]);
        }
        
        $attendance = $query->with([
                'user.profilePekerjaan.departemen',
                'user.profilePekerjaan.tempatKerja',
                'user.profilePekerjaan.jabatan',
                'user.profilePribadi'
            ])
            ->orderBy('tanggal', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'User attendance history retrieved successfully',
            'data' => $attendance,
        ]);
    }

    /**
     * Get all attendance records for today (for HRD/Admin)
     */
    public function getTodayAll(Request $request): JsonResponse
    {
        $today = Carbon::today();

        $attendance = Absensi::whereDate('tanggal', $today)
            ->with([
                'user.profilePekerjaan.departemen',
                'user.profilePekerjaan.tempatKerja',
                'user.profilePekerjaan.jabatan',
                'user.profilePribadi'
            ])
            ->orderBy('tanggal', 'desc')
            ->orderBy('check_in', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Today attendance records retrieved successfully',
            'data' => $attendance,
        ]);
    }

    /**
     * Create manual attendance record
     */
    public function createManual(Request $request): JsonResponse
    {
        $user = $request->user();

        $validatedData = $request->validate([
            'tanggal_mulai' => 'required|date',
            'durasi_hari' => 'required|integer|min:0',
            'status_absensi' => 'required|in:Sakit,Cuti',
            'keterangan_pendukung' => 'nullable|string|max:1000',
            'file_pendukung' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ], [
            'tanggal_mulai.required' => 'Tanggal mulai wajib diisi.',
            'tanggal_mulai.date' => 'Format tanggal tidak valid.',
            'durasi_hari.required' => 'Durasi hari wajib diisi.',
            'durasi_hari.integer' => 'Durasi hari harus berupa angka.',
            'durasi_hari.min' => 'Durasi hari minimal 0 (hari ini saja).',
            'status_absensi.required' => 'Status absensi wajib dipilih.',
            'status_absensi.in' => 'Status absensi hanya boleh: Sakit atau Cuti.',
            'keterangan_pendukung.string' => 'Keterangan harus berupa teks.',
            'keterangan_pendukung.max' => 'Keterangan maksimal 1000 karakter.',
            'file_pendukung.file' => 'File pendukung harus berupa file.',
            'file_pendukung.mimes' => 'Format file hanya boleh: jpg, jpeg, png, pdf.',
            'file_pendukung.max' => 'Ukuran file maksimal 2MB.',
        ]);

        try {
            DB::beginTransaction();

            $filePath = null;
            if ($request->hasFile('file_pendukung')) {
                $file = $request->file('file_pendukung');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $filePath = $file->storeAs('file_pendukung', $fileName, 'public');
            }

            $tanggalAwal = Carbon::parse($validatedData['tanggal_mulai']);
            $durasi = (int) $validatedData['durasi_hari'];
            $status = strtolower($validatedData['status_absensi']);
            $jumlahDisimpan = 0;

            for ($i = 0; $i <= $durasi; $i++) {
                $tanggalAbsen = $tanggalAwal->copy()->addDays($i);

                $sudahAbsen = Absensi::where('id_user', $user->id)
                    ->whereDate('tanggal', $tanggalAbsen)
                    ->exists();

                if (!$sudahAbsen) {
                    $attendance = Absensi::create([
                        'id_user' => $user->id,
                        'tanggal' => $tanggalAbsen,
                        'status' => $status,
                        'check_in' => Carbon::now(),
                        'check_out' => Carbon::now(),
                        'latitude_in' => null,
                        'longitude_in' => null,
                        'latitude_out' => null,
                        'longitude_out' => null,
                        'keterangan' => $validatedData['keterangan_pendukung'] ?? null,
                        'file_pendukung' => $filePath,
                    ]);

                    // Create log activity
                    LogAktivitasAbsensi::create([
                        'id_user' => $user->id,
                        'id_absensi' => $attendance->id,
                        'aksi' => 'manual_create',
                        'data_baru' => json_encode([
                            'tanggal' => Carbon::parse($attendance->tanggal)->format('Y-m-d'),
                            'status' => $attendance->status,
                            'keterangan' => $attendance->keterangan,
                            'file_pendukung' => $attendance->file_pendukung,
                        ]),
                    ]);

                    $jumlahDisimpan++;
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $jumlahDisimpan > 0
                    ? 'Berhasil menambahkan absensi ' . $validatedData['status_absensi'] . ' selama ' . $jumlahDisimpan . ' hari.'
                    : 'Semua tanggal sudah tercatat. Tidak ada data baru yang ditambahkan.',
                'data' => [
                    'jumlah_disimpan' => $jumlahDisimpan,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan absensi: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get attendance log by attendance ID
     */
    public function getLog(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        
        // Get attendance with trashed (soft deleted)
        $attendance = Absensi::withTrashed()
            ->where('id', $id)
            ->with([
                'user.profilePribadi',
                'logAktivitasAbsensi.user.profilePribadi'
            ])
            ->first();

        if (!$attendance) {
            return response()->json([
                'success' => false,
                'message' => 'Attendance record not found',
            ], 404);
        }

        // Check if user has permission to view this attendance
        // User can only view their own attendance unless they are admin/HRD
        $isAdmin = $user->hasAnyRole(['superadmin']);
        $isHRD = $user->hasAnyRole(['kepala hrd', 'staff hrd']);
        
        if (!$isAdmin && !$isHRD && $attendance->id_user !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to view this attendance log',
            ], 403);
        }

        // Format log activities
        $logs = $attendance->logAktivitasAbsensi->map(function ($log) {
            return [
                'id' => $log->id,
                'aksi' => $log->aksi,
                'data_lama' => $log->data_lama ? json_decode($log->data_lama, true) : null,
                'data_baru' => $log->data_baru ? json_decode($log->data_baru, true) : null,
                'user' => $log->user ? [
                    'id' => $log->user->id,
                    'nama_lengkap' => $log->user->profilePribadi->nama_lengkap ?? 'User tidak ditemukan',
                ] : null,
                'created_at' => $log->created_at->format('Y-m-d H:i:s'),
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Attendance log retrieved successfully',
            'data' => [
                'attendance' => [
                    'id' => $attendance->id,
                    'tanggal' => Carbon::parse($attendance->tanggal)->format('Y-m-d'),
                    'status' => $attendance->status,
                    'check_in' => $attendance->check_in ? $attendance->check_in->format('Y-m-d H:i:s') : null,
                    'check_out' => $attendance->check_out ? $attendance->check_out->format('Y-m-d H:i:s') : null,
                    'keterangan' => $attendance->keterangan,
                    'file_pendukung' => $attendance->file_pendukung,
                ],
                'user' => [
                    'id' => $attendance->user->id,
                    'nama_lengkap' => $attendance->user->profilePribadi->nama_lengkap ?? 'N/A',
                    'foto' => $attendance->user->profilePribadi->foto ?? null,
                ],
                'logs' => $logs,
            ],
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

