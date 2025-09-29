<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;

class RolesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Ambil semua data role beserta relasi users dan permissions
        // $roles: Collection -> Semua role beserta relasi users dan permissions
        $roles = Role::with(['users', 'permissions'])->get()->sortByDesc('created_at');

        // Ambil semua permission dan kelompokkan berdasarkan prefix (modul)
        // Contoh: 'user.create' → 'user' => ['create', 'read', ...]
        $permissions = Permission::all()->groupBy(function ($permission) {
            return explode('.', $permission->name)[0]; // Ambil prefix modul
        })->map(function ($groupedPermissions) {
            // Ambil hanya action-nya, misalnya dari 'user.create' → 'create'
            $actions = $groupedPermissions->map(function ($permission) {
                return explode('.', $permission->name)[1];
            });

            // Tentukan urutan preferensi
            $preferredOrder = ['create', 'read', 'update', 'delete'];

            // Urutkan berdasarkan urutan preferensi
            return collect($preferredOrder)->filter(function ($action) use ($actions) {
                return $actions->contains($action);
            })->values();
        });

        // Kirim data ke view
        return view('roles-and-permissions.roles', [
            'roles' => $roles,
            'permissions' => $permissions,
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
        $validate = $request->validate([
            'name' => 'required|unique:roles,name',
            'permissions' => 'array',
        ]);

        $role = Role::create([
            'name' => strtolower($request->name)
        ]);

        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        if($role){
            return redirect()->back()->with([
                'notifikasi' => 'Role '. $request->name .' berhasil ditambahkan',
                'type' => 'success'
            ]);
        }else{
            return redirect()->back()->with([
                'notifikasi' => 'Role '. $request->name .' gagal ditambahkan',
                'type' => 'error'
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show( $roles)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit( $roles)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $roles)
    {
        $validate = $request->validate([
            'name' => 'required|unique:roles,name,' . $roles,
            'permissions' => 'array',
        ]);

        $role = Role::findOrFail($roles);
        $role->name = strtolower($request->name);

        $role->syncPermissions($request->permissions);

        $role->save();


        if($role->save()){
            return redirect()->back()->with([
                'notifikasi' => 'Berhasil mengubah data',
                'type' => 'success'
            ]);
        }else{
            return redirect()->back()->with([
                'notifikasi' => 'gagal mengubah data',
                'type' => 'error'
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($roles)
    {
        $role = Role::findOrFail($roles);

        if ($role->count() < 1) {
            return redirect()->back()->with([
                'notifikasi' =>'Data tidak ditemukan!',
                'type'=>'error'
            ]);
        }
        if ($role->delete()) {
            return redirect()->back()->with([
                'notifikasi'=>"Berhasil menghapus data!",
                "type"=>"success"
            ]);
        }else{
            return redirect()->back()->with([
                'notifikasi'=>"Gagal menghapus data!",
                "type"=>"error",
            ]);
        }
    }
}
