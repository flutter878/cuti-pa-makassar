<?php

namespace App\Services;

use App\Models\ApprovalStage;
use App\Models\Cuti;
use App\Models\CutiAuditTrail;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ApprovalWorkflowService
{
    public function __construct(private readonly CutiService $cutiService) {}

    // ─────────────────────────────────────────────────────────
    // BUAT STAGES DARI ROUTING YANG DIPILIH ADMIN
    //
    // $stages = [
    //   [ 'user_id' => 5, 'label_tahap' => 'Atasan Langsung',
    //     'jenis_tindakan' => 'approval', 'perlu_ttd' => true ],
    //   [ 'user_id' => 3, 'label_tahap' => 'Ketua',
    //     'jenis_tindakan' => 'final_approval', 'perlu_ttd' => true ],
    // ]
    // ─────────────────────────────────────────────────────────
    public function buatStagesFromRouting(
        Cuti   $cuti,
        array  $stages,
        User   $admin,
        string $ip = ''
    ): void {
        if (! $cuti->bisaDiRouting()) {
            throw ValidationException::withMessages([
                'routing' => 'Pengajuan ini tidak dalam tahap penentuan routing.',
            ]);
        }

        if (empty($stages)) {
            throw ValidationException::withMessages([
                'stages' => 'Minimal harus ada satu tahap approval.',
            ]);
        }

        // Pastikan stages terakhir berjenis final_approval
        $lastStage = end($stages);
        if ($lastStage['jenis_tindakan'] !== 'final_approval') {
            throw ValidationException::withMessages([
                'stages' => 'Tahap terakhir harus berjenis Final Approval.',
            ]);
        }

        DB::transaction(function () use ($cuti, $stages, $admin, $ip) {
            // Hapus stages lama jika ada (misalnya re-routing)
            $cuti->approvalStages()->delete();

            $routingSummary = [];

            foreach ($stages as $urutan => $stage) {
                $user    = User::with('pegawai.jabatan')->findOrFail($stage['user_id']);
                $pegawai = $user->pegawai;

                ApprovalStage::create([
                    'cuti_id'          => $cuti->id,
                    'urutan'           => $urutan + 1,
                    'user_id'          => $user->id,
                    'pegawai_id'       => $pegawai?->id,
                    'nama_approver'    => $pegawai?->nama ?? $user->name,
                    'jabatan_approver' => $pegawai?->jabatan?->nama_jabatan ?? '-',
                    'label_tahap'      => $stage['label_tahap'],
                    'jenis_tindakan'   => $stage['jenis_tindakan'],
                    'perlu_ttd'        => $stage['perlu_ttd'] ?? true,
                    'status'           => 'pending',
                ]);

                $routingSummary[] = "Tahap " . ($urutan + 1) . ": "
                    . ($pegawai?->nama ?? $user->name)
                    . " (" . $stage['label_tahap'] . ")";
            }

            // Update status cuti ke menunggu_approval
            $cuti->update(['status' => 'menunggu_approval']);

            // Catat di audit trail
            CutiAuditTrail::catat(
                cutiId: $cuti->id,
                aktor:  $admin->name . ' (Admin)',
                aksi:   'MENENTUKAN_ROUTING',
                keterangan: implode(' → ', $routingSummary),
                userId: $admin->id,
                ip:     $ip
            );

            CutiAuditTrail::catat(
                cutiId: $cuti->id,
                aktor:  $admin->name . ' (Admin)',
                aksi:   'MENERUSKAN',
                keterangan: 'Pengajuan diteruskan ke tahap approval.',
                userId: $admin->id,
                ip:     $ip
            );
        });
    }

    // ─────────────────────────────────────────────────────────
    // AMBIL TAHAP AKTIF YANG BISA DILAKUKAN OLEH USER LOGIN
    // ─────────────────────────────────────────────────────────
    public function getTahapAktifUntukUser(Cuti $cuti, User $user): ?ApprovalStage
    {
        if (! $cuti->sedangDiApproval()) {
            return null;
        }

        return $cuti->approvalStages()
                    ->where('status', 'pending')
                    ->where('user_id', $user->id)
                    ->orderBy('urutan')
                    ->first();
    }

    // ─────────────────────────────────────────────────────────
    // PROSES TINDAKAN APPROVE / MENGETAHUI / PERTIMBANGAN
    // ─────────────────────────────────────────────────────────
    public function prosesTindakan(
        Cuti        $cuti,
        User        $user,
        ApprovalStage $stage,
        string      $aksi,  // 'approve' | 'reject' | 'return'
        ?string     $catatan = null,
        string      $ip = ''
    ): void {
        // Pastikan ini tahap yang sedang aktif (urutan paling kecil yang pending)
        $tahapAktif = $cuti->tahapAktif();

        if (! $tahapAktif || $tahapAktif->id !== $stage->id) {
            throw ValidationException::withMessages([
                'stage' => 'Bukan giliran Anda atau tahap ini belum aktif.',
            ]);
        }

        if ($stage->user_id !== $user->id) {
            throw ValidationException::withMessages([
                'stage' => 'Anda tidak ditugaskan pada tahap approval ini.',
            ]);
        }

        if (! $stage->isPending()) {
            throw ValidationException::withMessages([
                'stage' => 'Tahap ini sudah diproses sebelumnya.',
            ]);
        }

        match ($aksi) {
            'approve' => $this->doApprove($cuti, $user, $stage, $catatan, $ip),
            'reject'  => $this->doReject($cuti, $user, $stage, $catatan, $ip),
            'return'  => $this->doReturn($cuti, $user, $stage, $catatan, $ip),
            default   => throw ValidationException::withMessages(['aksi' => 'Aksi tidak dikenali.']),
        };
    }

    // ─────────────────────────────────────────────────────────
    // DO APPROVE — setujui tahap ini, lanjut ke tahap berikutnya
    // ─────────────────────────────────────────────────────────
    private function doApprove(
        Cuti          $cuti,
        User          $user,
        ApprovalStage $stage,
        ?string       $catatan,
        string        $ip
    ): void {
        DB::transaction(function () use ($cuti, $user, $stage, $catatan, $ip) {
            $stage->update([
                'status'           => 'approved',
                'catatan'          => $catatan,
                'tanggal_tindakan' => now(),
            ]);

            $aksiLabel = match ($stage->jenis_tindakan) {
                'mengetahui'     => 'MENGETAHUI',
                'pertimbangan'   => 'MEMBERIKAN_PERTIMBANGAN',
                'final_approval' => 'MENYETUJUI_FINAL',
                default          => 'MENYETUJUI',
            };

            $pegawaiNama = $user->pegawai?->nama ?? $user->name;
            $jabatan     = $user->pegawai?->jabatan?->nama_jabatan ?? '-';

            CutiAuditTrail::catat(
                cutiId: $cuti->id,
                aktor:  "{$pegawaiNama} ({$jabatan})",
                aksi:   $aksiLabel,
                keterangan: $catatan,
                userId: $user->id,
                ip:     $ip
            );

            // Cek apakah ini tahap terakhir (final_approval)
            if ($stage->isFinalApproval()) {
                // Kurangi saldo cuti
                $cuti->load('saldoDetail');
                $this->cutiService->kurangiSaldo($cuti);

                $cuti->update(['status' => 'disetujui']);

                CutiAuditTrail::catat(
                    cutiId: $cuti->id,
                    aktor:  'Sistem',
                    aksi:   'MEMBUAT_DOKUMEN',
                    keterangan: 'Pengajuan disetujui. Saldo cuti dikurangi. Dokumen final siap.',
                    userId: null,
                    ip:     $ip
                );
            } else {
                // Cek apakah masih ada tahap pending setelah ini
                $tahapBerikut = $cuti->approvalStages()
                                     ->where('status', 'pending')
                                     ->where('urutan', '>', $stage->urutan)
                                     ->orderBy('urutan')
                                     ->first();

                if (! $tahapBerikut) {
                    // Tidak ada tahap lagi — berarti sudah selesai (seharusnya tidak terjadi)
                    $cuti->update(['status' => 'disetujui']);
                }
                // Jika ada tahap berikut, status cuti tetap menunggu_approval
                // dan tahap berikutnya otomatis menjadi aktif (pending)
            }
        });
    }

    // ─────────────────────────────────────────────────────────
    // DO REJECT — tolak, workflow berhenti
    // ─────────────────────────────────────────────────────────
    private function doReject(
        Cuti          $cuti,
        User          $user,
        ApprovalStage $stage,
        ?string       $catatan,
        string        $ip
    ): void {
        if (empty(trim($catatan ?? ''))) {
            throw ValidationException::withMessages([
                'catatan' => 'Alasan penolakan wajib diisi.',
            ]);
        }

        DB::transaction(function () use ($cuti, $user, $stage, $catatan, $ip) {
            $stage->update([
                'status'           => 'rejected',
                'catatan'          => $catatan,
                'tanggal_tindakan' => now(),
            ]);

            // Skip semua tahap berikutnya
            $cuti->approvalStages()
                 ->where('status', 'pending')
                 ->where('urutan', '>', $stage->urutan)
                 ->update(['status' => 'skipped']);

            $cuti->update([
                'status'  => 'ditolak',
                'catatan' => $catatan,
            ]);

            $pegawaiNama = $user->pegawai?->nama ?? $user->name;
            $jabatan     = $user->pegawai?->jabatan?->nama_jabatan ?? '-';

            CutiAuditTrail::catat(
                cutiId: $cuti->id,
                aktor:  "{$pegawaiNama} ({$jabatan})",
                aksi:   'MENOLAK',
                keterangan: $catatan,
                userId: $user->id,
                ip:     $ip
            );
        });
    }

    // ─────────────────────────────────────────────────────────
    // DO RETURN — kembalikan ke pemohon untuk perbaikan
    // ─────────────────────────────────────────────────────────
    private function doReturn(
        Cuti          $cuti,
        User          $user,
        ApprovalStage $stage,
        ?string       $catatan,
        string        $ip
    ): void {
        if (empty(trim($catatan ?? ''))) {
            throw ValidationException::withMessages([
                'catatan' => 'Catatan/alasan pengembalian wajib diisi.',
            ]);
        }

        DB::transaction(function () use ($cuti, $user, $stage, $catatan, $ip) {
            $stage->update([
                'status'           => 'returned',
                'catatan'          => $catatan,
                'tanggal_tindakan' => now(),
            ]);

            // Reset semua stages (hapus) agar saat submit ulang Admin routing ulang
            $cuti->approvalStages()->delete();

            $cuti->update([
                'status'  => 'dikembalikan',
                'catatan' => $catatan,
            ]);

            $pegawaiNama = $user->pegawai?->nama ?? $user->name;
            $jabatan     = $user->pegawai?->jabatan?->nama_jabatan ?? '-';

            CutiAuditTrail::catat(
                cutiId: $cuti->id,
                aktor:  "{$pegawaiNama} ({$jabatan})",
                aksi:   'MENGEMBALIKAN',
                keterangan: $catatan,
                userId: $user->id,
                ip:     $ip
            );
        });
    }

    // ─────────────────────────────────────────────────────────
    // VERIFIKASI ADMIN (langkah 1 — cek data, isi nomor surat)
    // Setelah ini status → menunggu_routing
    // ─────────────────────────────────────────────────────────
    public function verifikasiAdmin(
        Cuti   $cuti,
        string $nomorSurat,
        string $masaKerja,
        User   $admin,
        string $ip = ''
    ): void {
        if (! $cuti->bisaDiprosesAdmin()) {
            throw ValidationException::withMessages([
                'status' => 'Pengajuan ini tidak dalam tahap verifikasi admin.',
            ]);
        }

        DB::transaction(function () use ($cuti, $nomorSurat, $masaKerja, $admin, $ip) {
            $cuti->update([
                'nomor_surat' => $nomorSurat,
                'masa_kerja'  => $masaKerja,
                'status'      => 'menunggu_routing',
            ]);

            CutiAuditTrail::catat(
                cutiId: $cuti->id,
                aktor:  $admin->name . ' (Admin)',
                aksi:   'MEMVERIFIKASI',
                keterangan: "Nomor surat: {$nomorSurat}. Masa kerja: {$masaKerja}.",
                userId: $admin->id,
                ip:     $ip
            );
        });
    }

    // ─────────────────────────────────────────────────────────
    // TOLAK ADMIN — tolak pengajuan sebelum routing
    // ─────────────────────────────────────────────────────────
    public function tolakAdmin(
        Cuti    $cuti,
        string  $catatan,
        User    $admin,
        string  $ip = ''
    ): void {
        if (! in_array($cuti->status, ['menunggu_verifikasi_admin', 'menunggu_routing'], true)) {
            throw ValidationException::withMessages([
                'status' => 'Pengajuan ini tidak dapat ditolak pada tahap ini.',
            ]);
        }

        DB::transaction(function () use ($cuti, $catatan, $admin, $ip) {
            $cuti->update([
                'status'  => 'ditolak',
                'catatan' => $catatan,
            ]);

            CutiAuditTrail::catat(
                cutiId: $cuti->id,
                aktor:  $admin->name . ' (Admin)',
                aksi:   'TOLAK_ADMIN',
                keterangan: $catatan,
                userId: $admin->id,
                ip:     $ip
            );
        });
    }

    // ─────────────────────────────────────────────────────────
    // CEK KUOTA CUTI PADA TANGGAL TERTENTU
    // Mengembalikan info: kuota, terpakai, sisa, status
    // ─────────────────────────────────────────────────────────
    public function cekKuota(string $tanggalMulai, string $tanggalSelesai, int $kuotaMax = 3): array
    {
        // Hitung berapa pegawai yang cutinya overlap dengan rentang ini
        $terpakai = Cuti::whereIn('status', ['menunggu_approval', 'disetujui'])
            ->where('tanggal_mulai', '<=', $tanggalSelesai)
            ->where('tanggal_selesai', '>=', $tanggalMulai)
            ->count();

        $sisa   = max(0, $kuotaMax - $terpakai);
        $status = $terpakai >= $kuotaMax ? 'PENUH' : 'TERSEDIA';

        return [
            'kuota'    => $kuotaMax,
            'terpakai' => $terpakai,
            'sisa'     => $sisa,
            'status'   => $status,
            'penuh'    => $terpakai >= $kuotaMax,
        ];
    }

    // ─────────────────────────────────────────────────────────
    // GET DAFTAR CUTI YANG AKTIF PADA SUATU TANGGAL
    // Untuk kalender admin
    // ─────────────────────────────────────────────────────────
    public function getCutiAktifPadaTanggal(string $tanggal): \Illuminate\Support\Collection
    {
        return Cuti::with(['pegawai.jabatan', 'jenisCuti'])
            ->whereIn('status', ['menunggu_approval', 'disetujui'])
            ->where('tanggal_mulai', '<=', $tanggal)
            ->where('tanggal_selesai', '>=', $tanggal)
            ->get();
    }

    // ─────────────────────────────────────────────────────────
    // SUBMIT ULANG SETELAH DIKEMBALIKAN
    // ─────────────────────────────────────────────────────────
    public function submitUlang(
        Cuti   $cuti,
        array  $data,
        User   $user,
        string $ip = ''
    ): void {
        if (! $cuti->bisaDiedit()) {
            throw ValidationException::withMessages([
                'status' => 'Pengajuan ini tidak dalam status dapat diedit.',
            ]);
        }

        DB::transaction(function () use ($cuti, $data, $user, $ip) {
            // Update data pengajuan
            $cuti->update(array_merge($data, [
                'status'  => 'menunggu_verifikasi_admin',
                'catatan' => null,
            ]));

            $pegawaiNama = $user->pegawai?->nama ?? $user->name;

            CutiAuditTrail::catat(
                cutiId: $cuti->id,
                aktor:  $pegawaiNama,
                aksi:   'SUBMIT_ULANG',
                keterangan: 'Pengajuan diperbaiki dan diajukan kembali.',
                userId: $user->id,
                ip:     $ip
            );
        });
    }
}
