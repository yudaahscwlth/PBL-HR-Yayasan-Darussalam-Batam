<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PengajuanCuti;
use App\Models\SlipGaji;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class NotificationController extends Controller
{
    /**
     * Get notifications for authenticated user
     * Generates notifications from pengajuan_cuti and slip_gaji tables without storing in DB
     */
    public function getNotifications(Request $request): JsonResponse
    {
        $userId = auth()->id();
        $notifications = [];
        $daysBack = 30; // Show notifications from last 30 days

        // Get leave notifications
        $leaves = PengajuanCuti::where('id_user', $userId)
            ->where('updated_at', '>=', Carbon::now()->subDays($daysBack))
            ->orderBy('updated_at', 'desc')
            ->get();

        foreach ($leaves as $leave) {
            $leaveDate = Carbon::parse($leave->tanggal_mulai)->format('d M Y');
            $status = $leave->status_pengajuan;
            
            // HRD Approval
            if ($status === 'disetujui hrd' || $status === 'disetujui hrd menunggu tinjauan dirpen') {
                $notifications[] = [
                    'id' => 'leave-' . $leave->id . '-hrd-approved',
                    'type' => 'leave_approved',
                    'category' => 'leave',
                    'title' => 'Cuti Disetujui HRD',
                    'message' => "Pengajuan cuti Anda untuk tanggal {$leaveDate} telah disetujui oleh HRD",
                    'timestamp' => $leave->updated_at->toIso8601String(),
                    'data' => [
                        'leave_id' => $leave->id,
                        'tanggal_mulai' => $leave->tanggal_mulai,
                        'tanggal_selesai' => $leave->tanggal_selesai,
                        'status' => $status,
                    ]
                ];
            }
            
            // HRD Rejection
            if ($status === 'ditolak hrd') {
                $notifications[] = [
                    'id' => 'leave-' . $leave->id . '-hrd-rejected',
                    'type' => 'leave_rejected',
                    'category' => 'leave',
                    'title' => 'Cuti Ditolak HRD',
                    'message' => "Pengajuan cuti Anda untuk tanggal {$leaveDate} ditolak oleh HRD",
                    'timestamp' => $leave->updated_at->toIso8601String(),
                    'data' => [
                        'leave_id' => $leave->id,
                        'tanggal_mulai' => $leave->tanggal_mulai,
                        'tanggal_selesai' => $leave->tanggal_selesai,
                        'status' => $status,
                    ]
                ];
            }

            // Kepala HRD Approval
            if ($status === 'disetujui kepala hrd' || $status === 'disetujui kepala hrd menunggu tinjauan dirpen') {
                $notifications[] = [
                    'id' => 'leave-' . $leave->id . '-kepala-hrd-approved',
                    'type' => 'leave_approved',
                    'category' => 'leave',
                    'title' => 'Cuti Disetujui Kepala HRD',
                    'message' => "Pengajuan cuti Anda untuk tanggal {$leaveDate} telah disetujui oleh Kepala HRD",
                    'timestamp' => $leave->updated_at->toIso8601String(),
                    'data' => [
                        'leave_id' => $leave->id,
                        'tanggal_mulai' => $leave->tanggal_mulai,
                        'tanggal_selesai' => $leave->tanggal_selesai,
                        'status' => $status,
                    ]
                ];
            }
            
            // Kepala HRD Rejection
            if ($status === 'ditolak kepala hrd') {
                $notifications[] = [
                    'id' => 'leave-' . $leave->id . '-kepala-hrd-rejected',
                    'type' => 'leave_rejected',
                    'category' => 'leave',
                    'title' => 'Cuti Ditolak Kepala HRD',
                    'message' => "Pengajuan cuti Anda untuk tanggal {$leaveDate} ditolak oleh Kepala HRD",
                    'timestamp' => $leave->updated_at->toIso8601String(),
                    'data' => [
                        'leave_id' => $leave->id,
                        'tanggal_mulai' => $leave->tanggal_mulai,
                        'tanggal_selesai' => $leave->tanggal_selesai,
                        'status' => $status,
                    ]
                ];
            }

            // Kepsek Approval
            if ($status === 'disetujui kepala sekolah' || $status === 'disetujui kepala sekolah menunggu tinjauan dirpen') {
                $notifications[] = [
                    'id' => 'leave-' . $leave->id . '-kepsek-approved',
                    'type' => 'leave_approved',
                    'category' => 'leave',
                    'title' => 'Cuti Disetujui Kepala Sekolah',
                    'message' => "Pengajuan cuti Anda untuk tanggal {$leaveDate} telah disetujui oleh Kepala Sekolah",
                    'timestamp' => $leave->updated_at->toIso8601String(),
                    'data' => [
                        'leave_id' => $leave->id,
                        'tanggal_mulai' => $leave->tanggal_mulai,
                        'tanggal_selesai' => $leave->tanggal_selesai,
                        'status' => $status,
                    ]
                ];
            }
            
            // Kepsek Rejection
            if ($status === 'ditolak kepala sekolah') {
                $notifications[] = [
                    'id' => 'leave-' . $leave->id . '-kepsek-rejected',
                    'type' => 'leave_rejected',
                    'category' => 'leave',
                    'title' => 'Cuti Ditolak Kepala Sekolah',
                    'message' => "Pengajuan cuti Anda untuk tanggal {$leaveDate} ditolak oleh Kepala Sekolah",
                    'timestamp' => $leave->updated_at->toIso8601String(),
                    'data' => [
                        'leave_id' => $leave->id,
                        'tanggal_mulai' => $leave->tanggal_mulai,
                        'tanggal_selesai' => $leave->tanggal_selesai,
                        'status' => $status,
                    ]
                ];
            }

            // Dirpen Approval
            if ($status === 'disetujui dirpen') {
                $notifications[] = [
                    'id' => 'leave-' . $leave->id . '-dirpen-approved',
                    'type' => 'leave_approved',
                    'category' => 'leave',
                    'title' => 'Cuti Disetujui Direktur Pendidikan',
                    'message' => "Pengajuan cuti Anda untuk tanggal {$leaveDate} telah disetujui oleh Direktur Pendidikan",
                    'timestamp' => $leave->updated_at->toIso8601String(),
                    'data' => [
                        'leave_id' => $leave->id,
                        'tanggal_mulai' => $leave->tanggal_mulai,
                        'tanggal_selesai' => $leave->tanggal_selesai,
                        'status' => $status,
                    ]
                ];
            }
            
            // Dirpen Rejection
            if ($status === 'ditolak dirpen') {
                $notifications[] = [
                    'id' => 'leave-' . $leave->id . '-dirpen-rejected',
                    'type' => 'leave_rejected',
                    'category' => 'leave',
                    'title' => 'Cuti Ditolak Direktur Pendidikan',
                    'message' => "Pengajuan cuti Anda untuk tanggal {$leaveDate} ditolak oleh Direktur Pendidikan",
                    'timestamp' => $leave->updated_at->toIso8601String(),
                    'data' => [
                        'leave_id' => $leave->id,
                        'tanggal_mulai' => $leave->tanggal_mulai,
                        'tanggal_selesai' => $leave->tanggal_selesai,
                        'status' => $status,
                    ]
                ];
            }
        }

        // Get slip gaji notifications
        $slipGaji = SlipGaji::where('id_user', $userId)
            ->where('created_at', '>=', Carbon::now()->subDays($daysBack))
            ->orderBy('created_at', 'desc')
            ->get();

        foreach ($slipGaji as $slip) {
            $slipDate = Carbon::parse($slip->tanggal)->format('F Y');
            
            $notifications[] = [
                'id' => 'slip-' . $slip->id,
                'type' => 'slip_gaji',
                'category' => 'slip_gaji',
                'title' => 'Slip Gaji Tersedia',
                'message' => "Slip gaji bulan {$slipDate} telah tersedia untuk Anda",
                'timestamp' => $slip->created_at->toIso8601String(),
                'data' => [
                    'slip_id' => $slip->id,
                    'tanggal' => $slip->tanggal,
                    'total_gaji' => $slip->total_gaji,
                ]
            ];
        }

        // Sort all notifications by timestamp (most recent first)
        usort($notifications, function($a, $b) {
            return strtotime($b['timestamp']) - strtotime($a['timestamp']);
        });

        return response()->json([
            'success' => true,
            'data' => $notifications,
            'count' => count($notifications)
        ]);
    }

    /**
     * Get verifier notifications for authenticated user
     * Shows leave requests that need to be verified/approved by the user
     */
    public function getVerifierNotifications(Request $request): JsonResponse
    {
        $user = auth()->user();
        $notifications = [];
        $daysBack = 30; // Show notifications from last 30 days

        // Determine which leave requests to show based on user role
        if ($user->hasRole('kepala sekolah')) {
            // Kepala sekolah sees leaves with status "ditinjau kepala sekolah" from their school
            $departemenId = $user->profilePekerjaan?->id_tempat_kerja;

            if ($departemenId) {
                $leaves = PengajuanCuti::whereHas('user.profilePekerjaan', function ($query) use ($departemenId) {
                    $query->where('id_tempat_kerja', $departemenId);
                })
                    ->where('status_pengajuan', 'ditinjau kepala sekolah')
                    ->where('created_at', '>=', Carbon::now()->subDays($daysBack))
                    ->with(['user.profilePribadi'])
                    ->orderBy('created_at', 'desc')
                    ->get();

                foreach ($leaves as $leave) {
                    $leaveDate = Carbon::parse($leave->tanggal_mulai)->format('d M Y');
                    $requesterName = $leave->user?->profilePribadi?->nama_lengkap ?? 'User';

                    $notifications[] = [
                        'id' => 'leave-verify-' . $leave->id,
                        'type' => 'leave_verification_needed',
                        'category' => 'leave',
                        'title' => 'Pengajuan Cuti Menunggu Verifikasi',
                        'message' => "{$requesterName} mengajukan cuti untuk tanggal {$leaveDate}",
                        'timestamp' => $leave->created_at->toIso8601String(),
                        'data' => [
                            'leave_id' => $leave->id,
                            'requester_name' => $requesterName,
                            'tanggal_mulai' => $leave->tanggal_mulai,
                            'tanggal_selesai' => $leave->tanggal_selesai,
                            'tipe_cuti' => $leave->tipe_cuti,
                            'status' => $leave->status_pengajuan,
                        ]
                    ];
                }
            }
        } elseif ($user->hasRole('staff hrd')) {
            // Staff HRD sees leaves with status "ditinjau hrd"
            $leaves = PengajuanCuti::where('status_pengajuan', 'ditinjau hrd')
                ->where('created_at', '>=', Carbon::now()->subDays($daysBack))
                ->with(['user.profilePribadi'])
                ->orderBy('created_at', 'desc')
                ->get();

            foreach ($leaves as $leave) {
                $leaveDate = Carbon::parse($leave->tanggal_mulai)->format('d M Y');
                $requesterName = $leave->user?->profilePribadi?->nama_lengkap ?? 'User';

                $notifications[] = [
                    'id' => 'leave-verify-' . $leave->id,
                    'type' => 'leave_verification_needed',
                    'category' => 'leave',
                    'title' => 'Pengajuan Cuti Menunggu Verifikasi HRD',
                    'message' => "{$requesterName} mengajukan cuti untuk tanggal {$leaveDate}",
                    'timestamp' => $leave->created_at->toIso8601String(),
                    'data' => [
                        'leave_id' => $leave->id,
                        'requester_name' => $requesterName,
                        'tanggal_mulai' => $leave->tanggal_mulai,
                        'tanggal_selesai' => $leave->tanggal_selesai,
                        'tipe_cuti' => $leave->tipe_cuti,
                        'status' => $leave->status_pengajuan,
                    ]
                ];
            }
        } elseif ($user->hasRole('kepala hrd')) {
            // Kepala HRD sees leaves with status "ditinjau kepala hrd"
            $leaves = PengajuanCuti::where('status_pengajuan', 'ditinjau kepala hrd')
                ->where('created_at', '>=', Carbon::now()->subDays($daysBack))
                ->with(['user.profilePribadi'])
                ->orderBy('created_at', 'desc')
                ->get();

            foreach ($leaves as $leave) {
                $leaveDate = Carbon::parse($leave->tanggal_mulai)->format('d M Y');
                $requesterName = $leave->user?->profilePribadi?->nama_lengkap ?? 'User';

                $notifications[] = [
                    'id' => 'leave-verify-' . $leave->id,
                    'type' => 'leave_verification_needed',
                    'category' => 'leave',
                    'title' => 'Pengajuan Cuti Menunggu Verifikasi Kepala HRD',
                    'message' => "{$requesterName} mengajukan cuti untuk tanggal {$leaveDate}",
                    'timestamp' => $leave->created_at->toIso8601String(),
                    'data' => [
                        'leave_id' => $leave->id,
                        'requester_name' => $requesterName,
                        'tanggal_mulai' => $leave->tanggal_mulai,
                        'tanggal_selesai' => $leave->tanggal_selesai,
                        'tipe_cuti' => $leave->tipe_cuti,
                        'status' => $leave->status_pengajuan,
                    ]
                ];
            }
        } elseif ($user->hasRole('kepala departemen')) {
            // Kepala departemen sees leaves from members of their department
            $departemenId = $user->profilePekerjaan?->id_departemen;

            if ($departemenId) {
                $leaves = PengajuanCuti::whereHas('user.profilePekerjaan', function ($query) use ($departemenId) {
                    $query->where('id_departemen', $departemenId);
                })
                    ->where('id_user', '!=', $user->id) // Exclude their own leaves
                    ->whereIn('status_pengajuan', [
                        'ditinjau kepala sekolah',
                        'ditinjau hrd',
                        'disetujui kepala sekolah',
                        'disetujui hrd',
                        'ditolak kepala sekolah',
                        'ditolak hrd'
                    ])
                    ->where('created_at', '>=', Carbon::now()->subDays($daysBack))
                    ->with(['user.profilePribadi'])
                    ->orderBy('created_at', 'desc')
                    ->get();

                foreach ($leaves as $leave) {
                    $leaveDate = Carbon::parse($leave->tanggal_mulai)->format('d M Y');
                    $requesterName = $leave->user?->profilePribadi?->nama_lengkap ?? 'User';

                    $notifications[] = [
                        'id' => 'leave-verify-' . $leave->id,
                        'type' => 'leave_verification_needed',
                        'category' => 'leave',
                        'title' => 'Pengajuan Cuti dari Anggota Departemen',
                        'message' => "{$requesterName} mengajukan cuti untuk tanggal {$leaveDate}",
                        'timestamp' => $leave->created_at->toIso8601String(),
                        'data' => [
                            'leave_id' => $leave->id,
                            'requester_name' => $requesterName,
                            'tanggal_mulai' => $leave->tanggal_mulai,
                            'tanggal_selesai' => $leave->tanggal_selesai,
                            'tipe_cuti' => $leave->tipe_cuti,
                            'status' => $leave->status_pengajuan,
                        ]
                    ];
                }
            }
        } elseif ($user->hasRole('direktur pendidikan')) {
            // Direktur pendidikan sees leaves that are waiting for their review
            $leaves = PengajuanCuti::whereIn('status_pengajuan', [
                'disetujui hrd menunggu tinjauan dirpen',
                'disetujui kepala hrd menunggu tinjauan dirpen',
                'disetujui kepala sekolah menunggu tinjauan dirpen',
                'ditinjau dirpen'
            ])
                ->where('created_at', '>=', Carbon::now()->subDays($daysBack))
                ->with(['user.profilePribadi'])
                ->orderBy('created_at', 'desc')
                ->get();

            foreach ($leaves as $leave) {
                $leaveDate = Carbon::parse($leave->tanggal_mulai)->format('d M Y');
                $requesterName = $leave->user?->profilePribadi?->nama_lengkap ?? 'User';

                $notifications[] = [
                    'id' => 'leave-verify-' . $leave->id,
                    'type' => 'leave_verification_needed',
                    'category' => 'leave',
                    'title' => 'Pengajuan Cuti Menunggu Tinjauan Direktur Pendidikan',
                    'message' => "{$requesterName} mengajukan cuti untuk tanggal {$leaveDate}",
                    'timestamp' => $leave->created_at->toIso8601String(),
                    'data' => [
                        'leave_id' => $leave->id,
                        'requester_name' => $requesterName,
                        'tanggal_mulai' => $leave->tanggal_mulai,
                        'tanggal_selesai' => $leave->tanggal_selesai,
                        'tipe_cuti' => $leave->tipe_cuti,
                        'status' => $leave->status_pengajuan,
                    ]
                ];
            }
        } elseif ($user->hasRole('kepala yayasan')) {
            // Kepala yayasan sees all important leaves (all statuses for overview)
            $leaves = PengajuanCuti::whereIn('status_pengajuan', [
                'ditinjau kepala sekolah',
                'ditinjau hrd',
                'ditinjau kepala hrd',
                'ditinjau dirpen',
                'disetujui hrd menunggu tinjauan dirpen',
                'disetujui kepala hrd menunggu tinjauan dirpen',
                'disetujui kepala sekolah menunggu tinjauan dirpen',
                'disetujui dirpen',
                'disetujui kepala sekolah',
                'disetujui hrd',
                'disetujui kepala hrd'
            ])
                ->where('created_at', '>=', Carbon::now()->subDays($daysBack))
                ->with(['user.profilePribadi'])
                ->orderBy('created_at', 'desc')
                ->get();

            foreach ($leaves as $leave) {
                $leaveDate = Carbon::parse($leave->tanggal_mulai)->format('d M Y');
                $requesterName = $leave->user?->profilePribadi?->nama_lengkap ?? 'User';

                $notifications[] = [
                    'id' => 'leave-verify-' . $leave->id,
                    'type' => 'leave_verification_needed',
                    'category' => 'leave',
                    'title' => 'Pengajuan Cuti',
                    'message' => "{$requesterName} mengajukan cuti untuk tanggal {$leaveDate} - Status: {$leave->status_pengajuan}",
                    'timestamp' => $leave->created_at->toIso8601String(),
                    'data' => [
                        'leave_id' => $leave->id,
                        'requester_name' => $requesterName,
                        'tanggal_mulai' => $leave->tanggal_mulai,
                        'tanggal_selesai' => $leave->tanggal_selesai,
                        'tipe_cuti' => $leave->tipe_cuti,
                        'status' => $leave->status_pengajuan,
                    ]
                ];
            }
        } elseif ($user->hasRole('superadmin')) {
            // Superadmin sees all pending leaves
            $leaves = PengajuanCuti::whereIn('status_pengajuan', [
                'ditinjau kepala sekolah',
                'ditinjau hrd',
                'ditinjau kepala hrd',
                'ditinjau dirpen',
                'disetujui hrd menunggu tinjauan dirpen',
                'disetujui kepala hrd menunggu tinjauan dirpen',
                'disetujui kepala sekolah menunggu tinjauan dirpen'
            ])
                ->where('created_at', '>=', Carbon::now()->subDays($daysBack))
                ->with(['user.profilePribadi'])
                ->orderBy('created_at', 'desc')
                ->get();

            foreach ($leaves as $leave) {
                $leaveDate = Carbon::parse($leave->tanggal_mulai)->format('d M Y');
                $requesterName = $leave->user?->profilePribadi?->nama_lengkap ?? 'User';

                $notifications[] = [
                    'id' => 'leave-verify-' . $leave->id,
                    'type' => 'leave_verification_needed',
                    'category' => 'leave',
                    'title' => 'Pengajuan Cuti Menunggu Verifikasi',
                    'message' => "{$requesterName} mengajukan cuti untuk tanggal {$leaveDate} - Status: {$leave->status_pengajuan}",
                    'timestamp' => $leave->created_at->toIso8601String(),
                    'data' => [
                        'leave_id' => $leave->id,
                        'requester_name' => $requesterName,
                        'tanggal_mulai' => $leave->tanggal_mulai,
                        'tanggal_selesai' => $leave->tanggal_selesai,
                        'tipe_cuti' => $leave->tipe_cuti,
                        'status' => $leave->status_pengajuan,
                    ]
                ];
            }
        }

        return response()->json([
            'success' => true,
            'data' => $notifications,
            'count' => count($notifications)
        ]);
    }
}
