<?php 
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Pegawai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user();
        $pegawai = $user->pegawai; 
        
        // Validasi jika pegawai tidak ditemukan
        if (!$pegawai) {
            return redirect()->back()->with([
                'notifikasi' => 'Data pegawai tidak ditemukan.',
                'type' => 'error'
            ]);
        }
        
        $nama_jabatan = $pegawai->jabatan->nama_jabatan ?? 'Jabatan tidak tersedia';
        $nama_departemen = $pegawai->departemen->nama_departemen ?? 'Departemen tidak tersedia';

        if ($user->role == 'hrd') {
            return view('admin.edit-profil', compact('pegawai', 'nama_departemen', 'nama_jabatan'));
        } elseif ($user->role == 'pegawai') {
            return view('karyawan.edit-profil', compact('pegawai', 'nama_departemen', 'nama_jabatan'));
        } elseif ($user->role == 'kepala_yayasan') {
            return view('kepala.edit-profil', compact('pegawai', 'nama_departemen', 'nama_jabatan'));
        } else {
            // Tambahkan fallback jika role tidak dikenali
            return redirect()->back()->with([
                'notifikasi' => 'Role pengguna tidak dikenali.',
                'type' => 'error'
            ]);
        }
    }

    public function update(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'tempat_lahir' => 'nullable|string|max:100',
            'tanggal_lahir' => 'nullable|date',
            'jenis_kelamin' => 'nullable|in:L,P',
            'alamat' => 'nullable|string|max:255',
            'no_hp' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $pegawai = Auth::user()->pegawai;

        if (!$pegawai) {
            return redirect()->back()->with([
                'notifikasi' => 'Data pegawai tidak ditemukan.',
                'type' => 'error'
            ]);
        }

        $data = $request->only([
            'nama', 'tempat_lahir', 'tanggal_lahir', 'jenis_kelamin',
            'alamat', 'no_hp', 'email'
        ]);

        // Handle upload foto jika ada
        if ($request->hasFile('foto')) {
            // Hapus foto lama jika ada
            if ($pegawai->foto && file_exists(public_path('uploads/pegawai/' . $pegawai->foto))) {
                unlink(public_path('uploads/pegawai/' . $pegawai->foto));
            }
            
            $file = $request->file('foto');
            $namaFile = time() . '_' . $file->getClientOriginalName();
            
            // Pastikan folder uploads/pegawai ada
            $uploadPath = public_path('uploads/pegawai');
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }
            
            $file->move($uploadPath, $namaFile);
            $data['foto'] = $namaFile;
        }

        try {
            $pegawai->update($data);
            
            return redirect()->back()->with([
                'success' => 'Profil berhasil diperbarui.',
                'notifikasi' => 'Profil berhasil diperbarui.',
                'type' => 'success'
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating profile: ' . $e->getMessage());
            
            return redirect()->back()->with([
                'notifikasi' => 'Terjadi kesalahan saat memperbarui profil: ' . $e->getMessage(),
                'type' => 'error'
            ]);
        }
    }

    /**
     * Update password pengguna
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
            'new_password_confirmation' => ['required', 'string', 'min:8'],
        ], [
            'current_password.required' => 'Password lama wajib diisi.',
            'new_password.required' => 'Password baru wajib diisi.',
            'new_password.min' => 'Password baru minimal 8 karakter.',
            'new_password.confirmed' => 'Konfirmasi password baru tidak cocok.',
            'new_password_confirmation.required' => 'Konfirmasi password baru wajib diisi.',
            'new_password_confirmation.min' => 'Konfirmasi password baru minimal 8 karakter.',
        ]);

        $user = Auth::user();

        // Verifikasi password lama
        if (!Hash::check($request->current_password, $user->password)) {
            return redirect()->back()->with([
                'notifikasi' => 'Password lama tidak sesuai.',
                'type' => 'error'
            ])->withInput();
        }

        // Cek apakah password baru sama dengan password lama
        if (Hash::check($request->new_password, $user->password)) {
            return redirect()->back()->with([
                'notifikasi' => 'Password baru tidak boleh sama dengan password lama.',
                'type' => 'error'
            ])->withInput();
        }

        try {
            // Fix: Get fresh user instance and update password
            $user = User::find(Auth::id());
            $user->password = Hash::make($request->new_password);
            $user->save();

            // Log aktivitas (optional)
            Log::info('Password updated for user: ' . $user->email . ' at ' . now());

            return redirect()->back()->with([
                'notifikasi' => 'Password berhasil diperbarui.',
                'type' => 'success'
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating password for user: ' . $user->email . '. Error: ' . $e->getMessage());
            
            return redirect()->back()->with([
                'notifikasi' => 'Terjadi kesalahan saat memperbarui password.',
                'type' => 'error'
            ]);
        }
    }

    /**
     * Reset password form (optional - untuk admin)
     */
    public function resetPassword(Request $request)
    {
        // Hanya admin yang bisa reset password user lain
        if (Auth::user()->role !== 'hrd') {
            return redirect()->back()->with([
                'notifikasi' => 'Akses ditolak.',
                'type' => 'error'
            ]);
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'new_password' => ['required', 'string', 'min:8'],
        ]);

        try {
            $user = User::findOrFail($request->user_id);
            $user->password = Hash::make($request->new_password);
            $user->save();

            Log::info('Password reset by admin for user: ' . $user->email . ' at ' . now());

            return redirect()->back()->with([
                'notifikasi' => 'Password berhasil direset.',
                'type' => 'success'
            ]);

        } catch (\Exception $e) {
            Log::error('Error resetting password. Error: ' . $e->getMessage());
            
            return redirect()->back()->with([
                'notifikasi' => 'Terjadi kesalahan saat mereset password.',
                'type' => 'error'
            ]);
        }
    }
}