<?php 
// App\Http\Controllers\Controller.php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $user;
    protected $pegawai;
    protected $nama_departemen;
    protected $nama_jabatan;

    public function __construct()
{
    $this->middleware(function ($request, $next) {
        $this->user = Auth::user();

        // Cek dulu user login atau tidak
        if ($this->user) {
            $this->pegawai = $this->user->pegawai;
            $this->nama_departemen = $this->pegawai->departemen?->nama_departemen ?? 'Tidak ada departemen';
            $this->nama_jabatan = $this->pegawai->jabatan?->nama_jabatan ?? 'Tidak ada jabatan';
        } else {
            $this->pegawai = null;
            $this->nama_departemen = null;
            $this->nama_jabatan = null;
        }

        return $next($request);
    });
}

}
