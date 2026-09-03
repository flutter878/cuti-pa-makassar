<?php

namespace App\Services;

use App\Models\Cuti;
use App\Models\CutiSaldoDetail;
use App\Models\DokumenCuti;
use App\Models\JenisCuti;
use App\Models\Pegawai;
use App\Models\SaldoCuti;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CutiService
{
    // ─────────────────────────────────────────────────────────
    // GENERATE NOMOR PENGAJUAN
    // Format: CUT/YYYY/MM/XXXX  →  CUT/2026/09/0001
    // ─────────────────────────────────────────────────────────
    public function generateNomor(): string
    {
        $prefix = 'CUT/' . now()->format('Y/m') . '/';

        $last = Cuti::where('nomor_pengajuan', 'like', $prefix . '%')
            ->orderByDesc('nomor_pengajuan')
            ->value('nomor_pengajuan');

        $urutan = $last
            ? (int) substr($last, -4) + 1
            : 1;

        return $prefix . str_pad($urutan, 4, '0', STR_PAD_LEFT);
    }

    // ─────────────────────────────────────────────────────────
    // VALIDASI H-3
    // Hanya berlaku untuk Cuti Tahunan (kode CT)
    // ─────────────────────────────────────────────────────────
    public function validasiH3(JenisCuti $jenisCuti, Carbon $tanggalMulai): void
    {
        if ($jenisCuti->kode !== 'CT') {
            return; // Hanya cuti tahunan yang kena aturan H-3
        }

        $hariIni   = now()->startOfDay();
        $batasAjukan = $tanggalMulai->copy()->subDays(3)->startOfDay();

        if ($hariIni->greaterThan($batasAjukan)) {
            throw ValidationException::withMessages([
                'tanggal_mulai' => "Cuti Tahunan harus diajukan minimal H-3 sebelum tanggal mulai cuti. "
                    . "Batas pengajuan untuk tanggal {$tanggalMulai->format('d/m/Y')} adalah "
                    . "{$batasAjukan->format('d/m/Y')}.",
            ]);
        }
    }

    // ─────────────────────────────────────────────────────────
    // VALIDASI & HITUNG SALDO (FIFO)
    // Kembalikan array detail pemakaian saldo per tahun
    // ─────────────────────────────────────────────────────────
    public function hitungSaldoFifo(Pegawai $pegawai, JenisCuti $jenisCuti, int $jumlahHari): array
    {
        // Jenis cuti yang tidak mengurangi saldo tidak perlu validasi
        if (! $jenisCuti->mengurangi_saldo) {
            return [];
        }

        $tahunSekarang = now()->year;
        $tahunTerlama  = $tahunSekarang - 2; // Saldo max 2 tahun ke belakang

        // Ambil saldo yang masih berlaku, urut dari tahun terlama (FIFO)
        $saldoList = SaldoCuti::where('pegawai_id', $pegawai->id)
            ->where('tahun', '>=', $tahunTerlama)
            ->where('tahun', '<=', $tahunSekarang)
            ->orderBy('tahun') // FIFO: terlama duluan
            ->get();

        // Hitung total sisa saldo yang tersedia
        $totalSisa = $saldoList->sum('sisa');

        if ($totalSisa < $jumlahHari) {
            throw ValidationException::withMessages([
                'jumlah_hari' => "Saldo cuti tidak mencukupi. Sisa saldo Anda: {$totalSisa} hari, "
                    . "dibutuhkan: {$jumlahHari} hari.",
            ]);
        }

        // Alokasikan pemakaian saldo secara FIFO
        $detail      = [];
        $sisaPerlu   = $jumlahHari;

        foreach ($saldoList as $saldo) {
            if ($sisaPerlu <= 0) break;

            $tersedia = $saldo->sisa;
            if ($tersedia <= 0) continue;

            $dipakai     = min($tersedia, $sisaPerlu);
            $detail[]    = [
                'saldo_cuti_id'    => $saldo->id,
                'jumlah_digunakan' => $dipakai,
            ];
            $sisaPerlu  -= $dipakai;
        }

        return $detail;
    }

    // ─────────────────────────────────────────────────────────
    // SIMPAN PENGAJUAN CUTI (transaksi penuh)
    // ─────────────────────────────────────────────────────────
    public function ajukan(
        Pegawai      $pegawai,
        array        $data,
        ?UploadedFile $lampiran = null
    ): Cuti {
        $jenisCuti   = JenisCuti::findOrFail($data['jenis_cuti_id']);
        $tanggalMulai = Carbon::parse($data['tanggal_mulai']);
        $jumlahHari  = (int) $data['jumlah_hari'];

        // Validasi H-3
        $this->validasiH3($jenisCuti, $tanggalMulai);

        // Validasi & hitung FIFO saldo
        $saldoDetail = $this->hitungSaldoFifo($pegawai, $jenisCuti, $jumlahHari);

        return DB::transaction(function () use ($pegawai, $data, $jenisCuti, $saldoDetail, $lampiran) {
            // Tentukan status awal: jika pegawai punya atasan langsung → menunggu_atasan
            // Jika tidak ada atasan langsung → langsung menunggu_ketua
            $pegawai->loadMissing('atasanLangsung');
            $statusAwal = $pegawai->atasan_langsung_id
                ? 'menunggu_atasan'
                : 'menunggu_ketua';

            // Buat record cuti
            $cuti = Cuti::create([
                'nomor_pengajuan' => $this->generateNomor(),
                'pegawai_id'      => $pegawai->id,
                'jenis_cuti_id'   => $jenisCuti->id,
                'tanggal_mulai'   => $data['tanggal_mulai'],
                'tanggal_selesai' => $data['tanggal_selesai'],
                'jumlah_hari'     => $data['jumlah_hari'],
                'alasan'          => $data['alasan'],
                'alamat_cuti'     => $data['alamat_cuti'],
                'no_telepon'      => $data['no_telepon'] ?? null,
                'status'          => $statusAwal,
                'tanggal_pengajuan' => now(),
            ]);

            // Simpan detail pemakaian saldo (FIFO)
            // Saldo BELUM dikurangi di sini — dikurangi hanya setelah Ketua setujui
            foreach ($saldoDetail as $detail) {
                CutiSaldoDetail::create([
                    'cuti_id'          => $cuti->id,
                    'saldo_cuti_id'    => $detail['saldo_cuti_id'],
                    'jumlah_digunakan' => $detail['jumlah_digunakan'],
                ]);
            }

            // Simpan lampiran jika ada
            if ($lampiran) {
                $path      = $lampiran->store('lampiran-cuti', 'public');
                $namaFile  = $lampiran->getClientOriginalName();
                $tipeFile  = $lampiran->getClientMimeType();

                DokumenCuti::create([
                    'cuti_id'   => $cuti->id,
                    'nama_file' => $namaFile,
                    'file_path' => $path,
                    'tipe_file' => $tipeFile,
                ]);
            }

            return $cuti;
        });
    }

    // ─────────────────────────────────────────────────────────
    // BATALKAN PENGAJUAN
    // Pegawai hanya bisa batalkan saat menunggu_atasan (belum diproses atasan)
    // Saldo tidak perlu dikembalikan karena belum dikurangi
    // ─────────────────────────────────────────────────────────
    public function batalkan(Cuti $cuti): void
    {
        if (! $cuti->bisaDibatalkan()) {
            throw ValidationException::withMessages([
                'status' => 'Pengajuan ini tidak dapat dibatalkan.',
            ]);
        }

        DB::transaction(function () use ($cuti) {
            // Saldo hanya dikurangi setelah Ketua setujui (disetujui),
            // jadi pembatalan sebelum itu tidak perlu kembalikan saldo.
            // Jika sudah disetujui penuh dan kemudian dibatalkan → kembalikan saldo
            if ($cuti->isDisetujui()) {
                $this->kembalikanSaldo($cuti);
            }

            $cuti->update(['status' => 'dibatalkan']);
        });
    }

    // ─────────────────────────────────────────────────────────
    // KURANGI SALDO (dipanggil saat approve)
    // ─────────────────────────────────────────────────────────
    public function kurangiSaldo(Cuti $cuti): void
    {
        foreach ($cuti->saldoDetail as $detail) {
            SaldoCuti::where('id', $detail->saldo_cuti_id)
                ->increment('terpakai', $detail->jumlah_digunakan);
        }
    }

    // ─────────────────────────────────────────────────────────
    // KEMBALIKAN SALDO (dipanggil saat batal/tolak setelah approve)
    // ─────────────────────────────────────────────────────────
    public function kembalikanSaldo(Cuti $cuti): void
    {
        foreach ($cuti->saldoDetail as $detail) {
            SaldoCuti::where('id', $detail->saldo_cuti_id)
                ->decrement('terpakai', $detail->jumlah_digunakan);
        }
    }

    // ─────────────────────────────────────────────────────────
    // HITUNG JUMLAH HARI KERJA
    // Saat ini hitung hari kalender biasa (bisa dikembangkan)
    // ─────────────────────────────────────────────────────────
    public function hitungHari(string $tanggalMulai, string $tanggalSelesai): int
    {
        $mulai   = Carbon::parse($tanggalMulai)->startOfDay();
        $selesai = Carbon::parse($tanggalSelesai)->startOfDay();

        if ($selesai->lessThan($mulai)) {
            return 0;
        }

        return $mulai->diffInDays($selesai) + 1;
    }
}
