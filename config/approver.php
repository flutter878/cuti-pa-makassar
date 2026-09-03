<?php

/**
 * Konfigurasi jabatan approver untuk alur persetujuan cuti.
 *
 * jabatan_atasan : jabatan yang berhak menyetujui di level 1 (atasan langsung)
 * jabatan_ketua  : jabatan yang berhak menyetujui di level 2 (final)
 *
 * Nama jabatan harus persis sama dengan data di tabel jabatan.
 */
return [

    /*
    |--------------------------------------------------------------------------
    | Jabatan Atasan Langsung (Level 1)
    |--------------------------------------------------------------------------
    | Pegawai yang menjabat sebagai Panitera atau Sekretaris berhak menjadi
    | atasan langsung dan menyetujui/menolak pengajuan cuti di tahap pertama.
    */
    'jabatan_atasan' => [
        'Panitera',
        'Sekretaris',
    ],

    /*
    |--------------------------------------------------------------------------
    | Jabatan Ketua (Level 2 — Final)
    |--------------------------------------------------------------------------
    | Ketua berhak menyetujui/menolak di tahap akhir.
    | Persetujuan Ketua = cuti resmi disetujui + saldo dikurangi.
    */
    'jabatan_ketua' => [
        'Ketua',
    ],

];
