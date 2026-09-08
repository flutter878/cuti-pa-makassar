<?php

namespace App\Services;

use App\Models\Cuti;
use App\Models\CutiSaldoDetail;
use App\Models\DokumenCuti;
use App\Models\HariLibur;
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
    // GENERATE NOMOR PENGAJUAN INTERNAL
    // Format: CUT/YYYY/MM/XXXX  →  CUT/2026/09/0001
    // (berbeda dengan nomor_surat yang diisi Admin)
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
    // HITUNG HARI KERJA
    // Skip: Sabtu (6), Minggu (0), hari libur aktif di tabel hari_libur
    // ─────────────────────────────────────────────────────────
    public function hitungHari(string $tanggalMulai, string $tanggalSelesai): int
    {
        $mulai   = Carbon::parse($tanggalMulai)->startOfDay();
        $selesai = Carbon::parse($tanggalSelesai)->startOfDay();

        if ($selesai->lessThan($mulai)) {
            return 0;
        }

        // Ambil daftar tanggal libur aktif dalam rentang yang diperlukan
        $hariLibur = HariLibur::tanggalLiburAktif(
            (int) $mulai->format('Y'),
            (int) $selesai->format('Y')
        );

        $hariKerja = 0;
        $current   = $mulai->copy();

        while ($current->lte($selesai)) {
            $dayOfWeek = $current->dayOfWeek; // 0=Minggu, 6=Sabtu

            $isSabtu  = ($dayOfWeek === Carbon::SATURDAY);
            $isMinggu = ($dayOfWeek === Carbon::SUNDAY);
            $isLibur  = in_array($current->format('Y-m-d'), $hariLibur, true);

            if (! $isSabtu && ! $isMinggu && ! $isLibur) {
                $hariKerja++;
            }

            $current->addDay();
        }

        return $hariKerja;
    }

    // ─────────────────────────────────────────────────────────
    // VALIDASI H-3
    // Hanya berlaku untuk Cuti Tahunan (kode CT)
    // ─────────────────────────────────────────────────────────
    public function validasiH3(JenisCuti $jenisCuti, Carbon $tanggalMulai): void
    {
        if ($jenisCuti->kode !== 'CT') {
            return;
        }

        $hariIni     = now()->startOfDay();
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
    // VALIDASI BATAS 30 HARI KE DEPAN
    // tanggal_mulai tidak boleh > today + 30 hari kalender
    // ─────────────────────────────────────────────────────────
    public function validasiBatas30Hari(Carbon $tanggalMulai): void
    {
        $batasMaksimal = now()->startOfDay()->addDays(30);

        if ($tanggalMulai->greaterThan($batasMaksimal)) {
            throw ValidationException::withMessages([
                'tanggal_mulai' => "Pengajuan cuti maksimal 30 hari ke depan dari tanggal hari ini. "
                    . "Tanggal maksimal yang diperbolehkan: {$batasMaksimal->format('d/m/Y')}.",
            ]);
        }
    }

    // ─────────────────────────────────────────────────────────
    // VALIDASI & HITUNG SALDO (FIFO)
    // ─────────────────────────────────────────────────────────
    public function hitungSaldoFifo(Pegawai $pegawai, JenisCuti $jenisCuti, int $jumlahHari): array
    {
        if (! $jenisCuti->mengurangi_saldo) {
            return [];
        }

        $tahunSekarang = now()->year;
        $tahunTerlama  = $tahunSekarang - 2;

        $saldoList = SaldoCuti::where('pegawai_id', $pegawai->id)
            ->where('tahun', '>=', $tahunTerlama)
            ->where('tahun', '<=', $tahunSekarang)
            ->orderBy('tahun') // FIFO: terlama duluan
            ->get();

        $totalSisa = $saldoList->sum('sisa');

        if ($totalSisa < $jumlahHari) {
            throw ValidationException::withMessages([
                'jumlah_hari' => "Saldo cuti tidak mencukupi. Sisa saldo Anda: {$totalSisa} hari, "
                    . "dibutuhkan: {$jumlahHari} hari.",
            ]);
        }

        $detail    = [];
        $sisaPerlu = $jumlahHari;

        foreach ($saldoList as $saldo) {
            if ($sisaPerlu <= 0) break;

            $tersedia = $saldo->sisa;
            if ($tersedia <= 0) continue;

            $dipakai  = min($tersedia, $sisaPerlu);
            $detail[] = [
                'saldo_cuti_id'    => $saldo->id,
                'jumlah_digunakan' => $dipakai,
            ];
            $sisaPerlu -= $dipakai;
        }

        return $detail;
    }

    // ─────────────────────────────────────────────────────────
    // SIMPAN PENGAJUAN CUTI
    // Status awal selalu menunggu_verifikasi_admin
    // ─────────────────────────────────────────────────────────
    public function ajukan(
        Pegawai       $pegawai,
        array         $data,
        ?UploadedFile $lampiran = null
    ): Cuti {
        $jenisCuti    = JenisCuti::findOrFail($data['jenis_cuti_id']);
        $tanggalMulai = Carbon::parse($data['tanggal_mulai']);
        $jumlahHari   = (int) $data['jumlah_hari'];

        // Validasi batas 30 hari ke depan
        $this->validasiBatas30Hari($tanggalMulai);

        // Validasi H-3 (Cuti Tahunan)
        $this->validasiH3($jenisCuti, $tanggalMulai);

        // Validasi & hitung FIFO saldo
        $saldoDetail = $this->hitungSaldoFifo($pegawai, $jenisCuti, $jumlahHari);

        return DB::transaction(function () use ($pegawai, $data, $jenisCuti, $saldoDetail, $lampiran) {
            // Status awal selalu menunggu_verifikasi_admin — admin harus verifikasi dulu
            $cuti = Cuti::create([
                'nomor_pengajuan'  => $this->generateNomor(),
                'pegawai_id'       => $pegawai->id,
                'jenis_cuti_id'    => $jenisCuti->id,
                'tanggal_mulai'    => $data['tanggal_mulai'],
                'tanggal_selesai'  => $data['tanggal_selesai'],
                'jumlah_hari'      => $data['jumlah_hari'],
                'alasan'           => $data['alasan'],
                'alamat_cuti'      => $data['alamat_cuti'],
                'no_telepon'       => $data['no_telepon'] ?? null,
                'status'           => 'menunggu_verifikasi_admin',
                'tanggal_pengajuan' => now(),
            ]);

            // Simpan detail pemakaian saldo (FIFO) — belum dikurangi
            foreach ($saldoDetail as $detail) {
                CutiSaldoDetail::create([
                    'cuti_id'          => $cuti->id,
                    'saldo_cuti_id'    => $detail['saldo_cuti_id'],
                    'jumlah_digunakan' => $detail['jumlah_digunakan'],
                ]);
            }

            // Simpan lampiran jika ada
            if ($lampiran) {
                $path     = $lampiran->store('lampiran-cuti', 'public');
                DokumenCuti::create([
                    'cuti_id'   => $cuti->id,
                    'nama_file' => $lampiran->getClientOriginalName(),
                    'file_path' => $path,
                    'tipe_file' => $lampiran->getClientMimeType(),
                ]);
            }

            return $cuti;
        });
    }

    // ─────────────────────────────────────────────────────────
    // VERIFIKASI ADMIN
    // Admin mengisi nomor_surat + masa_kerja, lalu teruskan ke atasan
    // ─────────────────────────────────────────────────────────
    public function verifikasiAdmin(Cuti $cuti, string $nomorSurat, string $masaKerja): void
    {
        if ($cuti->status !== 'menunggu_verifikasi_admin') {
            throw ValidationException::withMessages([
                'status' => 'Pengajuan ini tidak dalam tahap verifikasi admin.',
            ]);
        }

        DB::transaction(function () use ($cuti, $nomorSurat, $masaKerja) {
            $cuti->load('pegawai.atasanLangsung');

            // Jika pegawai punya atasan langsung → menunggu atasan
            // Jika tidak → langsung ke ketua
            $statusBerikut = $cuti->pegawai->atasan_langsung_id
                ? 'menunggu_persetujuan_atasan'
                : 'menunggu_persetujuan_ketua';

            $cuti->update([
                'nomor_surat' => $nomorSurat,
                'masa_kerja'  => $masaKerja,
                'status'      => $statusBerikut,
            ]);
        });
    }

    // ─────────────────────────────────────────────────────────
    // BATALKAN PENGAJUAN
    // ─────────────────────────────────────────────────────────
    public function batalkan(Cuti $cuti): void
    {
        if (! $cuti->bisaDibatalkan()) {
            throw ValidationException::withMessages([
                'status' => 'Pengajuan ini tidak dapat dibatalkan.',
            ]);
        }

        DB::transaction(function () use ($cuti) {
            if ($cuti->isDisetujui()) {
                $this->kembalikanSaldo($cuti);
            }
            $cuti->update(['status' => 'dibatalkan']);
        });
    }

    // ─────────────────────────────────────────────────────────
    // KURANGI SALDO (dipanggil saat Ketua setujui)
    // ─────────────────────────────────────────────────────────
    public function kurangiSaldo(Cuti $cuti): void
    {
        foreach ($cuti->saldoDetail as $detail) {
            SaldoCuti::where('id', $detail->saldo_cuti_id)
                ->increment('terpakai', $detail->jumlah_digunakan);
        }
    }

    // ─────────────────────────────────────────────────────────
    // KEMBALIKAN SALDO (saat batal/tolak setelah disetujui)
    // ─────────────────────────────────────────────────────────
    public function kembalikanSaldo(Cuti $cuti): void
    {
        foreach ($cuti->saldoDetail as $detail) {
            SaldoCuti::where('id', $detail->saldo_cuti_id)
                ->decrement('terpakai', $detail->jumlah_digunakan);
        }
    }
}
