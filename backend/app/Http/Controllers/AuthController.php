<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class authController extends Controller
{
    public function showLoginForm()
    {
        // Periksa apakah ada pengalihan tertunda (untuk SweetAlert)
        if (session('login_success') && session('redirect_to')) {
            $redirectTo = session('redirect_to');
            return view('auth.login', [
                'login_success' => true,
                'redirect_to' => $redirectTo
            ]);
        }

        return view('auth.login');
    }


    public function login(Request $request)
    {
        // Validasi data dengan pesan bahasa Indonesia
        $request->validate([
            'role' => ['required', 'string', 'in:hrd,kepala_yayasan,pegawai'],
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            // Pesan validasi bahasa Indonesia
            'role.required' => 'Peran harus dipilih.',
            'role.in' => 'Peran yang dipilih tidak valid.',
            'username.required' => 'Username harus diisi.',
            'username.string' => 'Username harus berupa teks.',
            'password.required' => 'Password harus diisi.',
            'password.string' => 'Password harus berupa teks.',
        ]);

        $credentials = $request->only('username', 'password');

        // Tambahkan role ke dalam credential
        if (Auth::attempt(array_merge($credentials, ['role' => $request->role]))) {
            // Autentikasi berhasil
            $user = Auth::user();

            // Periksa apakah data pegawai tersedia
            if (!$user->pegawai) {
                Auth::logout();
                throw ValidationException::withMessages([
                    'username' => ['Data pegawai tidak ditemukan untuk akun ini.'],
                ]);
            }

            $request->session()->regenerate();

                DB::table('log_activity')->insert([
                'id_user' => $user->id_user,
                'keterangan' => 'Login ke sistem',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Redirect sesuai peran
            $redirectTo = $this->getRedirectRoute($request->role);

            return redirect()->route('login')
                ->with('login_success', true)
                ->with('redirect_to', $redirectTo);
        }

        // Gagal login - pesan error bahasa Indonesia
        throw ValidationException::withMessages([
            'username' => ['Username atau password yang Anda masukkan salah.'],
        ]);
    }

    protected function getRedirectRoute($role)
    {
        return match ($role) {
            'hrd' => route('admin.index'),
            'kepala_yayasan' => route('kepala.index'),
            'pegawai' => route('karyawan.index'),
            default => route('home'),
        };
    }

    public function logout(Request $request)
    {

        DB::table('log_activity')->insert([
    'id_user' => Auth::id(),
    'keterangan' => 'Logout dari sistem',
    'created_at' => now(),
    'updated_at' => now(),
]);
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah berhasil logout.');
    }
}