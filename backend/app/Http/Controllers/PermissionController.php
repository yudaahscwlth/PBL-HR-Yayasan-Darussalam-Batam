<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $permissions = Permission::orderByDesc('created_at')  // Urutkan berdasarkan waktu terbaru
        ->orderByRaw('SUBSTRING_INDEX(name, ".", 1)')  // Urutkan berdasarkan nama modul (sebelum titik)
        ->get();

        $actions = ['create', 'read', 'update', 'delete'];

        // Kirim data ke view
        return view('roles-and-permissions.permission', [
            'permissions' => $permissions,
            'actions' => $actions
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|regex:/^[A-Za-z0-9]+(_[A-Za-z0-9]+)*$/',
            'permissions' => 'required|array',
        ], [
            'name.required' => 'Nama permission tidak boleh kosong.',
            'name.regex' => 'Format nama permission harus menggunakan underscore tanpa spasi atau simbol. Contoh: User_Manage atau user_Control',
            'permissions.required' => 'Minimal pilih satu jenis permission.',
            'permissions.array' => 'Format permissions tidak valid.',
        ]);

        $gagal = false;

        foreach ($request->permissions as $action) {
            // dd($action);
            $permission =Permission::firstOrCreate([
                'name' =>strtolower($request->name) . '.' . $action
            ]);

            if (!$permission->wasRecentlyCreated) {
                // Sudah ada, bisa dianggap gagal kalau kamu ingin 100% baru
                $gagal = true;
            }
        }

        if (!$gagal) {
            return redirect()->back()->with([
                'notifikasi' => 'Berhasil menambahkan permission.',
                'type' => 'success',
            ]);
        } else {
            return redirect()->back()->with([
                'notifikasi' => 'Sebagian Permission sudah ada sebelumnya.',
                'type' => 'info',
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request,$id_permission)
    {
        $validated = $request->validate([
            'name' => 'required|regex:/^[A-Za-z0-9]+(_[A-Za-z0-9]+)*$/',
            'action' => 'required|in:create,read,update,delete', // atau daftar action lain yang valid
        ], [
            'name.required' => 'Nama modul tidak boleh kosong.',
            'name.regex' => 'Format harus seperti user_control (underscore, tanpa spasi/simbol)',
            'action.required' => 'Aksi permission wajib diisi.',
            'action.in' => 'Aksi permission tidak valid.',
        ]);

        $permission = Permission::findOrFail($id_permission);

        $permission->name = $request->name . '.' . $request->action;

        if ($permission->save()) {
            return redirect()->back()->with([
                'notifikasi' => 'Permission berhasil diperbarui.',
                'type' => 'success',
            ]);
        } else {
            return redirect()->back()->with([
                'notifikasi' => 'Gagal memperbarui permission.',
                'type' => 'error',
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        $ids = explode(',', $request->id); // Sesuai input name di form

        try {
            $deleted = Permission::whereIn('id', $ids)->delete();

            if ($deleted === 0) {
                return redirect()->back()->with([
                    'notifikasi' => 'Tidak ada data yang dihapus!',
                    'type' => 'warning',
                ]);
            }

            return redirect()->back()->with([
                'notifikasi' => 'Berhasil menghapus ' . $deleted . ' data.',
                'type' => 'success',
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with([
                'notifikasi' => 'Gagal menghapus data!',
                'type' => 'error',
            ]);
        }
    }
}
