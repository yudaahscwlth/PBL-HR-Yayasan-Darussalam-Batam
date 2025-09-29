<?php

namespace App\Http\Controllers;

use App\Models\UserSosialMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserSosialMediaController extends Controller
{
    public function destroy($id_user_sosmed)
    {
        $userSosmed = UserSosialMedia::where('id', $id_user_sosmed)->first();

        if (!$userSosmed) {
            return redirect()->back()->with([
                'notifikasi' => 'Data tidak ditemukan!',
                'type' => 'error',
            ]);
        }

        if ($userSosmed->delete()) {
            return redirect()->back()->with([
                'notifikasi' => 'Berhasil menghapus data!',
                'type' => 'success',
            ]);
        } else {
            return redirect()->back()->with([
                'notifikasi' => 'Gagal menghapus data!',
                'type' => 'error',
            ]);
        }
    }
}
