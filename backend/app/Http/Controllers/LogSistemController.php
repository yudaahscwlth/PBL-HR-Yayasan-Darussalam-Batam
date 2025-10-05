<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class LogSistemController extends Controller
{
    public function index(Request $request)
    {
        // Query untuk mengambil data log activity
        $pegawai = Auth::user()->pegawai;
        $nama_departemen = $pegawai?->departemen?->nama_departemen ?? '';
        
        $query = DB::table('log_activity')
            ->select([
                'log_activity.id_log_activity',
                'log_activity.id_user',
                'log_activity.keterangan',
                'log_activity.created_at',
                'pegawai.nama as nama_user'
            ])
            ->leftJoin('user', 'log_activity.id_user', '=', 'user.id_user')
            ->leftJoin('pegawai', 'user.id_pegawai', '=', 'pegawai.id_pegawai')
            ->orderBy('log_activity.created_at', 'desc');

        // Filter berdasarkan pencarian
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('log_activity.keterangan', 'like', "%{$search}%")
                  ->orWhere('pegawai.nama', 'like', "%{$search}%");
            });
        }

        // Filter berdasarkan tanggal
        if ($request->has('tanggal_mulai') && !empty($request->tanggal_mulai)) {
            $query->whereDate('log_activity.created_at', '>=', $request->tanggal_mulai);
        }

        if ($request->has('tanggal_selesai') && !empty($request->tanggal_selesai)) {
            $query->whereDate('log_activity.created_at', '<=', $request->tanggal_selesai);
        }

        // Pagination
        $logs = $query->paginate(15);

        return view('admin.log-sistem.index', compact('logs', 'pegawai', 'nama_departemen'));
    }

    public function destroy($id)
    {
        try {
            DB::table('log_activity')->where('id_log_activity', $id)->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Log berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus log: ' . $e->getMessage()
            ]);
        }
    }

    public function clearAll()
    {
        try {
            DB::table('log_activity')->truncate();
            
            return response()->json([
                'success' => true,
                'message' => 'Semua log berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus semua log: ' . $e->getMessage()
            ]);
        }
    }
}