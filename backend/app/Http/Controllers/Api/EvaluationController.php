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

        // Check if user is HRD, admin, direktur pendidikan, or kepala yayasan (can view all evaluations)
        if ($user->hasAnyRole(['kepala hrd', 'staff hrd', 'superadmin', 'kepala yayasan', 'direktur pendidikan'])) {
            $evaluations = Evaluasi::with(['user.profilePribadi', 'kategoriEvaluasi', 'penilai.profilePribadi', 'tahunAjaran'])
                ->orderBy('created_at', 'desc')
                ->get();
        } elseif ($user->hasRole('kepala sekolah')) {
            // Kepala sekolah hanya melihat evaluasi dari tenaga pendidik di sekolah yang sama
            $tempatKerjaId = $user->profilePekerjaan?->id_tempat_kerja;

            if ($tempatKerjaId) {
                $evaluations = Evaluasi::whereHas('user', function ($query) use ($tempatKerjaId) {
                    // EXCLUSION: Cannot access superadmin and kepala yayasan evaluations
                    $query->whereDoesntHave('roles', function ($roleQuery) {
                        $roleQuery->whereIn('name', ['superadmin', 'kepala yayasan']);
                    });
                    // Check if user is tenaga pendidik
                    $query->whereHas('roles', function ($roleQuery) {
                        $roleQuery->where('name', 'tenaga pendidik');
                    });
                    // Check if user is from same workplace
                    $query->whereHas('profilePekerjaan', function ($pkQuery) use ($tempatKerjaId) {
                        $pkQuery->where('id_tempat_kerja', $tempatKerjaId);
                    });
                })
                    ->with(['user.profilePribadi', 'kategoriEvaluasi', 'penilai.profilePribadi', 'tahunAjaran'])
                    ->orderBy('created_at', 'desc')
                    ->get();
            } else {
                $evaluations = collect([]);
            }
        } elseif ($user->hasRole('kepala departemen')) {
            // Kepala departemen hanya melihat evaluasi dari departemen yang sama
            $departemenId = $user->profilePekerjaan?->id_departemen;

            if ($departemenId) {
                $evaluations = Evaluasi::whereHas('user', function ($query) use ($departemenId) {
                    // EXCLUSION: Cannot access superadmin and kepala yayasan evaluations
                    $query->whereDoesntHave('roles', function ($roleQuery) {
                        $roleQuery->whereIn('name', ['superadmin', 'kepala yayasan']);
                    });
                    // Check if user is from same department
                    $query->whereHas('profilePekerjaan', function ($pkQuery) use ($departemenId) {
                        $pkQuery->where('id_departemen', $departemenId);
                    });
                })
                    ->with(['user.profilePribadi', 'kategoriEvaluasi', 'penilai.profilePribadi', 'tahunAjaran'])
                    ->orderBy('created_at', 'desc')
                    ->get();
            } else {
                $evaluations = collect([]);
            }
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
        // Allow: HRD, Superadmin, Kepala Sekolah, and Kepala Departemen
        if (!$user->hasAnyRole(['kepala hrd', 'staff hrd', 'superadmin', 'kepala sekolah', 'kepala departemen'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to create evaluations',
            ], 403);
        }

        $request->validate([
            'id_user' => 'required|exists:users,id',
            'id_tahun_ajaran' => 'required|exists:tahun_ajarans,id',
            'evaluations' => 'required|array',
            'evaluations.*.id_kategori' => 'required|exists:kategori_evaluasis,id',
            'evaluations.*.nilai' => 'required|numeric|min:0|max:100',
            'evaluations.*.catatan' => 'nullable|string',
        ]);

        $createdEvaluations = [];

        foreach ($request->evaluations as $evalData) {
            // Check if evaluation already exists for this user, year, and category
            $existingEvaluation = Evaluasi::where('id_user', $request->id_user)
                ->where('id_tahun_ajaran', $request->id_tahun_ajaran)
                ->where('id_kategori', $evalData['id_kategori'])
                ->first();

            if ($existingEvaluation) {
                $existingEvaluation->update([
                    'nilai' => $evalData['nilai'],
                    'catatan' => $evalData['catatan'] ?? null,
                    'id_penilai' => $user->id,
                ]);
                $createdEvaluations[] = $existingEvaluation;
            } else {
                $evaluation = Evaluasi::create([
                    'id_user' => $request->id_user,
                    'id_penilai' => $user->id,
                    'id_tahun_ajaran' => $request->id_tahun_ajaran,
                    'id_kategori' => $evalData['id_kategori'],
                    'nilai' => $evalData['nilai'],
                    'catatan' => $evalData['catatan'] ?? null,
                ]);
                $createdEvaluations[] = $evaluation;
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Evaluations submitted successfully',
            'data' => $createdEvaluations,
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
        // Allow: HRD, Superadmin, Kepala Sekolah, and Kepala Departemen
        if (!$user->hasAnyRole(['kepala hrd', 'staff hrd', 'superadmin', 'kepala sekolah', 'kepala departemen'])) {
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
            ->with(['kategoriEvaluasi', 'penilai.profilePribadi', 'tahunAjaran'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Personal evaluations retrieved successfully',
            'data' => $evaluations,
        ]);
    }

    /**
     * Check if evaluation exists for a specific user and academic year
     */
    public function checkExists(Request $request): JsonResponse
    {
        $request->validate([
            'id_user' => 'required|exists:users,id',
            'id_tahun_ajaran' => 'required|exists:tahun_ajarans,id',
        ]);

        $evaluations = Evaluasi::where('id_user', $request->id_user)
            ->where('id_tahun_ajaran', $request->id_tahun_ajaran)
            ->with('kategoriEvaluasi')
            ->get();

        $exists = $evaluations->count() > 0;

        // Format evaluations data for frontend
        $evaluationData = [];
        $catatan = '';

        if ($exists) {
            foreach ($evaluations as $index => $eval) {
                $evaluationData[$eval->id_kategori] = $eval->nilai;
                if ($index === 0 && $eval->catatan) {
                    $catatan = $eval->catatan;
                }
            }
        }

        return response()->json([
            'success' => true,
            'exists' => $exists,
            'evaluations' => $evaluationData,
            'catatan' => $catatan,
        ]);
    }
}
