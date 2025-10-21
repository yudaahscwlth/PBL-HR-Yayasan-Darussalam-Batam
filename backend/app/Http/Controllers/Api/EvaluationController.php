<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Evaluasi;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class EvaluationController extends Controller
{
    /**
     * Display a listing of evaluations
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Check if user is HRD or admin
        if ($user->hasAnyRole(['kepala hrd', 'staff hrd', 'superadmin'])) {
            $evaluations = Evaluasi::with(['user.profilePribadi', 'kategoriEvaluasi'])
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            $evaluations = Evaluasi::where('id_user', $user->id)
                ->with(['kategoriEvaluasi'])
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return response()->json([
            'success' => true,
            'message' => 'Evaluations retrieved successfully',
            'data' => $evaluations,
        ]);
    }

    /**
     * Store a newly created evaluation
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Check if user has permission to create evaluations
        if (!$user->hasAnyRole(['kepala hrd', 'staff hrd', 'superadmin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to create evaluations',
            ], 403);
        }

        $request->validate([
            'id_user' => 'required|exists:users,id',
            'id_kategori_evaluasi' => 'required|exists:kategori_evaluasi,id',
            'nilai' => 'required|numeric|min:0|max:100',
            'keterangan' => 'nullable|string',
        ]);

        $evaluation = Evaluasi::create([
            'id_user' => $request->id_user,
            'id_kategori_evaluasi' => $request->id_kategori_evaluasi,
            'nilai' => $request->nilai,
            'keterangan' => $request->keterangan,
            'created_by' => $user->id,
        ]);

        $evaluation->load(['user.profilePribadi', 'kategoriEvaluasi']);

        return response()->json([
            'success' => true,
            'message' => 'Evaluation created successfully',
            'data' => $evaluation,
        ], 201);
    }

    /**
     * Display the specified evaluation
     */
    public function show(Evaluasi $evaluation): JsonResponse
    {
        $evaluation->load(['user.profilePribadi', 'kategoriEvaluasi']);

        return response()->json([
            'success' => true,
            'message' => 'Evaluation retrieved successfully',
            'data' => $evaluation,
        ]);
    }

    /**
     * Update the specified evaluation
     */
    public function update(Request $request, Evaluasi $evaluation): JsonResponse
    {
        $user = $request->user();
        
        // Check if user has permission to update evaluations
        if (!$user->hasAnyRole(['kepala hrd', 'staff hrd', 'superadmin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to update evaluations',
            ], 403);
        }

        $request->validate([
            'id_kategori_evaluasi' => 'sometimes|exists:kategori_evaluasi,id',
            'nilai' => 'sometimes|numeric|min:0|max:100',
            'keterangan' => 'nullable|string',
        ]);

        $evaluation->update($request->all());
        $evaluation->load(['user.profilePribadi', 'kategoriEvaluasi']);

        return response()->json([
            'success' => true,
            'message' => 'Evaluation updated successfully',
            'data' => $evaluation,
        ]);
    }

    /**
     * Remove the specified evaluation
     */
    public function destroy(Request $request, Evaluasi $evaluation): JsonResponse
    {
        $user = $request->user();
        
        // Check if user has permission to delete evaluations
        if (!$user->hasAnyRole(['kepala hrd', 'staff hrd', 'superadmin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to delete evaluations',
            ], 403);
        }

        $evaluation->delete();

        return response()->json([
            'success' => true,
            'message' => 'Evaluation deleted successfully',
        ]);
    }

    /**
     * Get personal evaluations
     */
    public function getPersonal(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $evaluations = Evaluasi::where('id_user', $user->id)
            ->with(['kategoriEvaluasi'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Personal evaluations retrieved successfully',
            'data' => $evaluations,
        ]);
    }
}
