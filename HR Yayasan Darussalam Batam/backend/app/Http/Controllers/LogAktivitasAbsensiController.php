<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use Illuminate\Http\Request;

class LogAktivitasAbsensiController extends Controller
{
    public function showLogAbsenPage($id_absensi){
        $absensi = Absensi::withTrashed()->where('id',$id_absensi)->first();

        return view('general.log-absensi',[
            'dataAbsensi' => $absensi,
        ]);
    }
}
