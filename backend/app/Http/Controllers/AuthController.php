<?php

namespace App\Http\Controllers;

use App\Helper\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function showLoginPage()
    {
        return view('login');
    }

    public function loginProcess(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required',
            'password' => 'required',
        ]);

        $credentials = [
            'email' => $request->email,
            'password' => $request->password,
        ];

        if (Auth::attempt($credentials)){
            $user = Auth::user();
            $request->session()->regenerate();
            switch (true) {
                case $user->hasAnyRole(['staff hrd','kepala hrd','direktur pendidikan','kepala yayasan','superadmin']):
                    // dd($user);
                    return redirect()->route('hrd.dashboard.page')->with([
                        'notifikasi' => 'Selamat Datang Admin',
                        'type' => 'success',
                    ]);
                case $user->hasAnyRole(['tenaga pendidik','kepala sekolah','kepala departemen']):
                    return redirect()->route('pegawai.dashboard.page')->with([
                        'notifikasi' => 'Selamat Datang Pegawai',
                        'type' => 'success',
                    ]);
                // Tambahkan case lain jika kamu punya role tambahan
                default:
                    return redirect()->route('pegawai.dashboard.page')->with([
                        'notifikasi' => 'Selamat Datang!',
                        'type' => 'success',
                    ]);
            }
        }

        return redirect()->back()->withInput()->with([
            'notifikasi' => 'Login Failed!',
            'type' => 'error',
        ]);
    }

    public function logout(Request $request): RedirectResponse{
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login.page')->with([
            'notifikasi' => 'Anda berhasil logout !',
            'type' => 'success'
        ]);
    }
}
