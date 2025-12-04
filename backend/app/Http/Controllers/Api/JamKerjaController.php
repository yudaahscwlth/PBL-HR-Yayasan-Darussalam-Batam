<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JamKerja;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JamKerjaController extends Controller
{
    /**
     * List jam kerja untuk satu jabatan.
     */
    public function index(Request $request, int $jabatanId): JsonResponse
    {
        $user = $request->user();

        if (!$this->userCanManage($user)) {
            return $this->unauthorizedResponse();
        }

        $jamKerja = JamKerja::where('id_jabatan', $jabatanId)
            ->orderByRaw("FIELD(hari, 'senin','selasa','rabu','kamis','jumat','sabtu','minggu')")
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Jam kerja retrieved successfully',
            'data' => $jamKerja,
        ]);
    }

    /**
     * Simpan jam kerja baru untuk beberapa hari sekaligus.
     */
    public function store(Request $request, int $jabatanId): JsonResponse
    {
        $user = $request->user();

        if (!$this->userCanManage($user)) {
            return $this->unauthorizedResponse();
        }

        $validated = $request->validate([
            'jam_masuk'   => 'nullable',
            'jam_pulang'  => 'nullable|after:jam_masuk',
            'is_libur'    => 'required|in:0,1',
            'keterangan'  => 'nullable|string|max:255',
            'hari'        => 'required|array|min:1',
            'hari.*'      => 'in:senin,selasa,rabu,kamis,jumat,sabtu,minggu',
        ]);

        $created = [];
        $duplicates = [];

        foreach ($validated['hari'] as $hari) {
            $exists = JamKerja::where('id_jabatan', $jabatanId)
                ->where('hari', $hari)
                ->exists();

            if ($exists) {
                $duplicates[] = $hari;
                continue;
            }

            $isSunday = $hari === 'minggu';
            $isLibur = $isSunday ? true : (bool) $validated['is_libur'];

            $created[] = JamKerja::create([
                'id_jabatan' => $jabatanId,
                'hari'       => $hari,
                'jam_masuk'  => $isLibur ? null : $validated['jam_masuk'],
                'jam_pulang' => $isLibur ? null : $validated['jam_pulang'],
                'is_libur'   => $isLibur,
                'keterangan' => $isLibur
                    ? ($validated['keterangan'] ?? 'Libur')
                    : ($validated['keterangan'] ?? null),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => count($duplicates)
                ? 'Sebagian jam kerja disimpan, namun beberapa hari sudah memiliki jam kerja.'
                : 'Jam kerja created successfully',
            'data' => $created,
            'duplicates' => $duplicates,
        ], 201);
    }

    /**
     * Perbarui satu record jam kerja.
     */
    public function update(Request $request, int $jabatanId, int $jamKerjaId): JsonResponse
    {
        $user = $request->user();

        if (!$this->userCanManage($user)) {
            return $this->unauthorizedResponse();
        }

        $validated = $request->validate([
            'jam_masuk'   => 'nullable',
            'jam_pulang'  => 'nullable|after:jam_masuk',
            'is_libur'    => 'required|in:0,1',
            'keterangan'  => 'nullable|string|max:255',
        ]);

        $jamKerja = JamKerja::where('id_jabatan', $jabatanId)
            ->where('id', $jamKerjaId)
            ->firstOrFail();

        $isSunday = $jamKerja->hari === 'minggu';
        $isLibur = $isSunday ? true : (bool) $validated['is_libur'];

        $jamKerja->update([
            'jam_masuk'  => $isLibur ? null : $validated['jam_masuk'],
            'jam_pulang' => $isLibur ? null : $validated['jam_pulang'],
            'is_libur'   => $isLibur,
            'keterangan' => $isLibur
                ? ($validated['keterangan'] ?? 'Libur')
                : ($validated['keterangan'] ?? null),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Jam kerja updated successfully',
            'data' => $jamKerja,
        ]);
    }

    /**
     * Hapus satu record jam kerja.
     */
    public function destroy(Request $request, int $jabatanId, int $jamKerjaId): JsonResponse
    {
        $user = $request->user();

        if (!$this->userCanManage($user)) {
            return $this->unauthorizedResponse();
        }

        $jamKerja = JamKerja::where('id_jabatan', $jabatanId)
            ->where('id', $jamKerjaId)
            ->firstOrFail();

        $jamKerja->delete();

        return response()->json([
            'success' => true,
            'message' => 'Jam kerja deleted successfully',
        ]);
    }

    private function userCanManage($user): bool
    {
        if (!$user) {
            return false;
        }

        if (!$user->relationLoaded('roles')) {
            $user->load('roles');
        }

        return $user->hasAnyRole(['kepala hrd', 'staff hrd', 'superadmin']);
    }

    private function unauthorizedResponse(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized to manage jam kerja',
        ], 403);
    }
}



