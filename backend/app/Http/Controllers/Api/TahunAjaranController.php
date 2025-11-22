<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TahunAjaranController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $tahunAjaran = TahunAjaran::orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'message' => 'List Data Tahun Ajaran',
            'data' => $tahunAjaran
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'nama' => 'required|string',
            'semester' => 'required|string',
            'is_aktif' => 'boolean'
        ]);

        // If setting to active, deactivate others
        if ($request->is_aktif) {
            TahunAjaran::where('is_aktif', true)->update(['is_aktif' => false]);
        }

        $tahunAjaran = TahunAjaran::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Tahun Ajaran Created',
            'data' => $tahunAjaran
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(TahunAjaran $tahunAjaran): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Detail Data Tahun Ajaran',
            'data' => $tahunAjaran
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TahunAjaran $tahunAjaran): JsonResponse
    {
        $request->validate([
            'nama' => 'string',
            'semester' => 'string',
            'is_aktif' => 'boolean'
        ]);

        // If setting to active, deactivate others
        if ($request->has('is_aktif') && $request->is_aktif) {
            TahunAjaran::where('id', '!=', $tahunAjaran->id)
                ->where('is_aktif', true)
                ->update(['is_aktif' => false]);
        }

        $tahunAjaran->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Tahun Ajaran Updated',
            'data' => $tahunAjaran
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TahunAjaran $tahunAjaran): JsonResponse
    {
        $tahunAjaran->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tahun Ajaran Deleted'
        ]);
    }
}
