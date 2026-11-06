<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PDF; 

class LaporanController extends Controller
{
    public function generatePDF()
    {
        // Data buatan sementara (dummy data) yang akan dikirim ke tampilan PDF
        $data = [
            'title' => 'Laporan Pegawai',
            'tanggal' => date('d-m-Y'),
            'pegawai' => [
                ['nama' => 'Andi', 'jabatan' => 'Manager'],
                ['nama' => 'Budi', 'jabatan' => 'Staff'],
            ]
        ];

        $pdf = PDF::loadView('laporan.pegawai', $data);

        return $pdf->download('laporan-pegawai.pdf'); 

    }
}