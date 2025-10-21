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
            'jenis_cuti' => $request->jenis_cuti,
            'alasan' => $request->alasan,
            'status' => 'pending',
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

        if ($leave->status !== 'pending') {
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

        $leave->update($request->all());

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

        if ($leave->status !== 'pending') {
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
    public function approve(Request $request, PengajuanCuti $leave): JsonResponse
    {
        $user = $request->user();
        
        // Check if user has permission to approve
        if (!$user->hasAnyRole(['kepala hrd', 'staff hrd', 'superadmin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to approve leave requests',
            ], 403);
        }

        if ($leave->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Leave request is not pending',
            ], 400);
        }

        $leave->update([
            'status' => 'approved',
            'approved_by' => $user->id,
            'approved_at' => Carbon::now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Leave request approved successfully',
            'data' => $leave,
        ]);
    }

    /**
     * Reject leave request
     */
    public function reject(Request $request, PengajuanCuti $leave): JsonResponse
    {
        $user = $request->user();
        
        // Check if user has permission to reject
        if (!$user->hasAnyRole(['kepala hrd', 'staff hrd', 'superadmin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to reject leave requests',
            ], 403);
        }

        if ($leave->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Leave request is not pending',
            ], 400);
        }

        $request->validate([
            'reason' => 'required|string',
        ]);

        $leave->update([
            'status' => 'rejected',
            'approved_by' => $user->id,
            'approved_at' => Carbon::now(),
            'keterangan' => $request->reason,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Leave request rejected successfully',
            'data' => $leave,
        ]);
    }
}
