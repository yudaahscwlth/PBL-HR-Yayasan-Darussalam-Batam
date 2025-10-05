<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Pegawai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
  public function index()
{
    $currentUser = Auth::user();
    $pegawai = $currentUser->pegawai;
    $nama_departemen = $pegawai->departemen->nama_departemen ?? 'N/A'; // Perbaikan dari $currentPegawai


    // Ambil semua user dengan relasi pegawai, departemen, dan jabatan
    $users = User::with(['pegawai.departemen', 'pegawai.jabatan'])
    ->orderBy('username')
    ->paginate(10)
    ->withQueryString();

    // Ambil pegawai yang belum memiliki user
    $pegawaiTanpaUser = Pegawai::whereDoesntHave('user')
        ->with(['departemen', 'jabatan'])
        ->get();

    return view('admin.user.index', compact(
        'users',
        'pegawaiTanpaUser',
        'nama_departemen',
        'pegawai'
    ));
}


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = Auth::user();
        $pegawai = $user->pegawai;
        $nama_departemen = $pegawai->departemen->nama_departemen ?? 'N/A';
        $pegawaiTanpaUser = Pegawai::whereDoesntHave('user')
            ->with(['departemen', 'jabatan'])
            ->get();

        return view('admin.user.create', compact('pegawaiTanpaUser','nama_departemen','pegawai'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255|unique:user',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|in:kepala_yayasan,pegawai,hrd',
            'id_pegawai' => 'required|exists:pegawai,id_pegawai|unique:user,id_pegawai'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Get pegawai data to determine role if not provided
        $pegawai = Pegawai::with('jabatan')->find($request->id_pegawai);
        $finalRole = $request->role;
        
        // If role is not provided or is default, determine from jabatan
        if (!$request->role || $request->role === 'pegawai') {
            $finalRole = $this->determineRoleFromJabatan($pegawai->jabatan->nama_jabatan ?? '');
        }

        User::create([
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'role' => $finalRole,
            'id_pegawai' => $request->id_pegawai,
        ]);

        return redirect()->route('admin.user.index')
            ->with('success', 'User berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        
        $user = User::with('pegawai')->findOrFail($id);
        $pegawai = $user->pegawai;
        $nama_departemen = $pegawai->departemen->nama_departemen ?? 'N/A';
        return view('admin.user.edit', compact('user','pegawai','nama_departemen'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255|unique:user,username,' . $id . ',id_user',
            'password' => 'nullable|string|min:6|confirmed',
            'role' => 'required|in:admin,pegawai',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $updateData = [
            'username' => $request->username,
            'role' => $request->role,
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        return redirect()->route('admin.user.index')
            ->with('success', 'User berhasil diupdate.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'User berhasil dihapus.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus user.'
            ], 500);
        }
    }

   /**
 * Create user for specific pegawai with detailed debugging
 */
public function createForPegawai(Request $request)
{
    // Debug: Log request data
    Log::info('createForPegawai Request Data:', $request->all());
    
    try {
        // Step 1: Basic validation
        $validator = Validator::make($request->all(), [
            'id_pegawai' => 'required|integer|exists:pegawai,id_pegawai'
        ]);

        if ($validator->fails()) {
            Log::error('Validation failed:', $validator->errors()->toArray());
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal: ' . $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 400);
        }

        // Step 2: Get pegawai data
        $pegawai = Pegawai::find($request->id_pegawai);
        if (!$pegawai) {
            Log::error('Pegawai not found:', ['id_pegawai' => $request->id_pegawai]);
            return response()->json([
                'success' => false,
                'message' => 'Pegawai tidak ditemukan.'
            ], 404);
        }

        Log::info('Pegawai found:', [
            'id' => $pegawai->id_pegawai,
            'nama' => $pegawai->nama,
            'email' => $pegawai->email
        ]);

        // Step 3: Check if user already exists
        $existingUser = User::where('id_pegawai', $request->id_pegawai)->first();
        if ($existingUser) {
            Log::warning('User already exists:', [
                'id_pegawai' => $request->id_pegawai,
                'username' => $existingUser->username
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Pegawai sudah memiliki user dengan username: ' . $existingUser->username
            ], 400);
        }

        // Step 4: Generate username
        $username = $this->generateUsername($pegawai);
        Log::info('Generated username:', ['username' => $username]);

        // Step 5: Determine role based on jabatan
        $jabatanName = $pegawai->jabatan->nama_jabatan ?? 'Unknown';
        $role = $this->determineRoleFromJabatan($jabatanName);
        
        Log::info('Role determination:', [
            'jabatan' => $jabatanName,
            'determined_role' => $role
        ]);
        
        // Create user
        $userData = [
            'username' => $username,
            'password' => Hash::make('pegawai123'),
            'role' => $role,
            'id_pegawai' => $request->id_pegawai,
        ];

        Log::info('Creating user with data:', $userData);

        $user = User::create($userData);

        Log::info('User created successfully:', [
            'user_id' => $user->id_user ?? $user->id,
            'username' => $user->username
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User berhasil dibuat dengan username: ' . $username . ' dan role: ' . $role,
            'data' => [
                'username' => $username,
                'password' => 'pegawai123',
                'role' => $role,
                'jabatan' => $jabatanName
            ]
        ]);

    } catch (\Exception $e) {
        Log::error('Error in createForPegawai:', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            'debug' => [
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]
        ], 500);
    }
}

/**
 * Generate username from pegawai data
 */
private function generateUsername($pegawai)
{
    $username = null;
    
    // Try to generate from email
    if (!empty($pegawai->email) && strpos($pegawai->email, '@') !== false) {
        $emailParts = explode('@', $pegawai->email);
        $username = $emailParts[0];
    } else {
        // Fallback to name
        $username = strtolower(str_replace(' ', '', $pegawai->nama));
    }
    
    // Clean username (only alphanumeric)
    $username = preg_replace('/[^a-zA-Z0-9]/', '', $username);
    
    // Ensure username is not empty
    if (empty($username)) {
        $username = 'user' . $pegawai->id_pegawai;
    }
    
    // Make sure username is unique
    $originalUsername = $username;
    $counter = 1;
    while (User::where('username', $username)->exists()) {
        $username = $originalUsername . $counter;
        $counter++;
    }
    
    return $username;
}

/**
 * Determine user role based on jabatan name
 */
private function determineRoleFromJabatan($namaJabatan)
{
    // Mapping jabatan ke role
    $jabatanToRole = [
        'Kepala Departemen' => 'kepala_yayasan',
        'Kepala HRD' => 'hrd',
        'Staff HRD' => 'hrd',
        'Staff IT' => 'pegawai',
        // Default untuk jabatan lainnya
    ];
    
    // Cari role berdasarkan nama jabatan
    foreach ($jabatanToRole as $jabatan => $role) {
        if (stripos($namaJabatan, $jabatan) !== false) {
            return $role;
        }
    }
    
    // Default role jika tidak ditemukan mapping
    return 'pegawai';
}
}