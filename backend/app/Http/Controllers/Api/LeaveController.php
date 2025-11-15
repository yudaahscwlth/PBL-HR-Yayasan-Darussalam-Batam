<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PengajuanCuti;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class LeaveController extends Controller
{
    /**
     * Display a listing of leave requests
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Check if user is HRD or admin
        if ($user->hasAnyRole(['kepala hrd', 'staff hrd', 'superadmin'])) {
            $leaves = PengajuanCuti::with(['user.profilePribadi'])
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            $leaves = PengajuanCuti::where('id_user', $user->id)
                ->with(['user.profilePribadi'])
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return response()->json([
            'success' => true,
            'message' => 'Leave requests retrieved successfully',
            'data' => $leaves,
        ]);
    }

    /**
     * Store a newly created leave request
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $request->validate([
            'tanggal_mulai' => 'required|date|after_or_equal:today',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'jenis_cuti' => 'required|string|max:255',
            'alasan' => 'required|string',
        ]);

        $leave = PengajuanCuti::create([
            'id_user' => $user->id,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'tipe_cuti' => strtolower($request->jenis_cuti),
            'alasan_pendukung' => $request->alasan,
            'status_pengajuan' => 'ditinjau kepala sekolah',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Leave request submitted successfully',
            'data' => $leave,
        ], 201);
    }

    /**
     * Display the specified leave request
     */
    public function show(PengajuanCuti $leave): JsonResponse
    {
        $leave->load(['user.profilePribadi']);

        return response()->json([
            'success' => true,
            'message' => 'Leave request retrieved successfully',
            'data' => $leave,
        ]);
    }

    /**
     * Update the specified leave request
     */
    public function update(Request $request, PengajuanCuti $leave): JsonResponse
    {
        $user = $request->user();
        
        // Only allow user to update their own pending requests
        if ($leave->id_user !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to update this leave request',
            ], 403);
        }

        // Check if leave request can still be updated
        $allowedStatuses = ['ditinjau kepala sekolah', 'ditinjau hrd', 'ditinjau kepala hrd'];
        if (!in_array($leave->status_pengajuan, $allowedStatuses)) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot update approved or rejected leave request',
            ], 400);
        }

        $request->validate([
            'tanggal_mulai' => 'sometimes|date|after_or_equal:today',
            'tanggal_selesai' => 'sometimes|date|after_or_equal:tanggal_mulai',
            'jenis_cuti' => 'sometimes|string|max:255',
            'alasan' => 'sometimes|string',
        ]);

        $updateData = [];
        if ($request->has('tanggal_mulai')) $updateData['tanggal_mulai'] = $request->tanggal_mulai;
        if ($request->has('tanggal_selesai')) $updateData['tanggal_selesai'] = $request->tanggal_selesai;
        if ($request->has('jenis_cuti')) $updateData['tipe_cuti'] = strtolower($request->jenis_cuti);
        if ($request->has('alasan')) $updateData['alasan_pendukung'] = $request->alasan;

        $leave->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Leave request updated successfully',
            'data' => $leave,
        ]);
    }

    /**
     * Remove the specified leave request
     */
    public function destroy(Request $request, PengajuanCuti $leave): JsonResponse
    {
        $user = $request->user();
        
        // Only allow user to delete their own pending requests
        if ($leave->id_user !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to delete this leave request',
            ], 403);
        }

        // Check if leave request can still be deleted
        $allowedStatuses = ['ditinjau kepala sekolah', 'ditinjau hrd', 'ditinjau kepala hrd'];
        if (!in_array($leave->status_pengajuan, $allowedStatuses)) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete approved or rejected leave request',
            ], 400);
        }

        $leave->delete();

        return response()->json([
            'success' => true,
            'message' => 'Leave request deleted successfully',
        ]);
    }

    /**
     * Approve leave request
     */
    public function approve(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        
        // Find the leave request by ID
        $leave = PengajuanCuti::find($id);
        
        if (!$leave) {
            return response()->json([
                'success' => false,
                'message' => 'Leave request not found',
            ], 404);
        }
        
        // Check if user has permission to approve
        if (!$user->hasAnyRole(['kepala hrd', 'staff hrd', 'superadmin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to approve leave requests',
            ], 403);
        }

        // Determine approval status based on user role and leave type
        if ($user->hasRole('kepala hrd')) {
            // For cuti tahunan, status should be "disetujui kepala hrd menunggu tinjauan dirpen"
            if (strtolower($leave->tipe_cuti) === 'cuti tahunan') {
                $newStatus = 'disetujui kepala hrd menunggu tinjauan dirpen';
            } else {
                $newStatus = 'disetujui kepala hrd';
            }
        } else {
            // Staff HRD: For cuti tahunan, status should be "disetujui hrd menunggu tinjauan dirpen"
            if (strtolower($leave->tipe_cuti) === 'cuti tahunan') {
                $newStatus = 'disetujui hrd menunggu tinjauan dirpen';
            } else {
                $newStatus = 'disetujui hrd';
            }
        }

        // Get komentar from request if provided
        $komentar = $request->input('komentar', null);

        // Prepare update data
        $updateData = [
            'status_pengajuan' => $newStatus,
        ];

        if ($komentar !== null && $komentar !== '') {
            $updateData['komentar'] = $komentar;
        }

        // Update in database using update method to ensure it's an UPDATE query, not INSERT
        $updated = $leave->update($updateData);
        
        if (!$updated) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update leave request status',
            ], 500);
        }

        // Refresh model and reload relationships to get latest data from database
        $leave->refresh();
        $leave->load(['user.profilePribadi']);

        return response()->json([
            'success' => true,
            'message' => 'Leave request approved successfully',
            'data' => $leave,
        ]);
    }

    /**
     * Reject leave request
     */
    public function reject(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        
        // Find the leave request by ID
        $leave = PengajuanCuti::find($id);
        
        if (!$leave) {
            return response()->json([
                'success' => false,
                'message' => 'Leave request not found',
            ], 404);
        }
        
        // Check if user has permission to reject
        if (!$user->hasAnyRole(['kepala hrd', 'staff hrd', 'superadmin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to reject leave requests',
            ], 403);
        }

        $request->validate([
            'reason' => 'sometimes|string',
        ]);

        // Determine rejection status based on user role
        $newStatus = 'ditolak hrd';
        if ($user->hasRole('kepala hrd')) {
            $newStatus = 'ditolak kepala hrd';
        }

        // Prepare update data
        $updateData = [
            'status_pengajuan' => $newStatus,
            'komentar' => $request->reason ?? 'Ditolak',
        ];

        // Update in database using update method to ensure it's an UPDATE query, not INSERT
        $updated = $leave->update($updateData);
        
        if (!$updated) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update leave request status',
            ], 500);
        }

        // Refresh model and reload relationships to get latest data from database
        $leave->refresh();
        $leave->load(['user.profilePribadi']);

        return response()->json([
            'success' => true,
            'message' => 'Leave request rejected successfully',
            'data' => $leave,
        ]);
    }
}
