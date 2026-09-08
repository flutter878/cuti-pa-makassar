<?php

namespace Database\Seeders;

use App\Models\RoutingTemplate;
use App\Models\RoutingTemplateStage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoutingTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [

            // ── Staf Biasa ────────────────────────────────────────
            [
                'nama'             => 'Alur Staf Biasa',
                'kategori_jabatan' => 'staf',
                'stages' => [
                    ['label' => 'Atasan Langsung / Kasubbag', 'kategori_approver' => 'kasubbag',  'jenis_tindakan' => 'approval',       'perlu_ttd' => true],
                    ['label' => 'Panitera / Sekretaris',       'kategori_approver' => 'panitera',  'jenis_tindakan' => 'mengetahui',     'perlu_ttd' => false],
                    ['label' => 'Ketua',                        'kategori_approver' => 'ketua',     'jenis_tindakan' => 'final_approval', 'perlu_ttd' => true],
                ],
            ],

            // ── Kepala Sub Bagian ─────────────────────────────────
            [
                'nama'             => 'Alur Kepala Sub Bagian (Kasubbag)',
                'kategori_jabatan' => 'kasubbag',
                'stages' => [
                    ['label' => 'Sekretaris',  'kategori_approver' => 'sekretaris', 'jenis_tindakan' => 'approval',       'perlu_ttd' => true],
                    ['label' => 'Ketua',       'kategori_approver' => 'ketua',      'jenis_tindakan' => 'final_approval', 'perlu_ttd' => true],
                ],
            ],

            // ── Panitera Muda ─────────────────────────────────────
            [
                'nama'             => 'Alur Panitera Muda',
                'kategori_jabatan' => 'panitera_muda',
                'stages' => [
                    ['label' => 'Panitera', 'kategori_approver' => 'panitera', 'jenis_tindakan' => 'approval',       'perlu_ttd' => true],
                    ['label' => 'Ketua',    'kategori_approver' => 'ketua',    'jenis_tindakan' => 'final_approval', 'perlu_ttd' => true],
                ],
            ],

            // ── Pejabat Fungsional ────────────────────────────────
            [
                'nama'             => 'Alur Pejabat Fungsional',
                'kategori_jabatan' => 'pejabat_fungsional',
                'stages' => [
                    ['label' => 'Panitera / Sekretaris', 'kategori_approver' => 'panitera', 'jenis_tindakan' => 'approval',       'perlu_ttd' => true],
                    ['label' => 'Ketua',                  'kategori_approver' => 'ketua',    'jenis_tindakan' => 'final_approval', 'perlu_ttd' => true],
                ],
            ],

            // ── Panitera Pengganti ────────────────────────────────
            [
                'nama'             => 'Alur Panitera Pengganti',
                'kategori_jabatan' => 'panitera_pengganti',
                'stages' => [
                    ['label' => 'Panitera Muda / Atasan Kepaniteraan', 'kategori_approver' => 'panitera_muda', 'jenis_tindakan' => 'approval',       'perlu_ttd' => true],
                    ['label' => 'Panitera',                             'kategori_approver' => 'panitera',     'jenis_tindakan' => 'approval',       'perlu_ttd' => true],
                    ['label' => 'Ketua',                                'kategori_approver' => 'ketua',        'jenis_tindakan' => 'final_approval', 'perlu_ttd' => true],
                ],
            ],

            // ── Jurusita ──────────────────────────────────────────
            [
                'nama'             => 'Alur Jurusita',
                'kategori_jabatan' => 'jurusita',
                'stages' => [
                    ['label' => 'Panitera Muda / Atasan Kepaniteraan', 'kategori_approver' => 'panitera_muda', 'jenis_tindakan' => 'approval',       'perlu_ttd' => true],
                    ['label' => 'Panitera',                             'kategori_approver' => 'panitera',     'jenis_tindakan' => 'approval',       'perlu_ttd' => true],
                    ['label' => 'Ketua',                                'kategori_approver' => 'ketua',        'jenis_tindakan' => 'final_approval', 'perlu_ttd' => true],
                ],
            ],

            // ── Jurusita Pengganti ────────────────────────────────
            [
                'nama'             => 'Alur Jurusita Pengganti',
                'kategori_jabatan' => 'jurusita_pengganti',
                'stages' => [
                    ['label' => 'Panitera Muda / Atasan Kepaniteraan', 'kategori_approver' => 'panitera_muda', 'jenis_tindakan' => 'approval',       'perlu_ttd' => true],
                    ['label' => 'Panitera',                             'kategori_approver' => 'panitera',     'jenis_tindakan' => 'approval',       'perlu_ttd' => true],
                    ['label' => 'Ketua',                                'kategori_approver' => 'ketua',        'jenis_tindakan' => 'final_approval', 'perlu_ttd' => true],
                ],
            ],

            // ── Hakim ─────────────────────────────────────────────
            [
                'nama'             => 'Alur Hakim',
                'kategori_jabatan' => 'hakim',
                'stages' => [
                    ['label' => 'Ketua', 'kategori_approver' => 'ketua', 'jenis_tindakan' => 'final_approval', 'perlu_ttd' => true],
                ],
            ],

            // ── Wakil Ketua ───────────────────────────────────────
            [
                'nama'             => 'Alur Wakil Ketua',
                'kategori_jabatan' => 'wakil_ketua',
                'stages' => [
                    ['label' => 'Ketua', 'kategori_approver' => 'ketua', 'jenis_tindakan' => 'final_approval', 'perlu_ttd' => true],
                ],
            ],

        ];

        DB::transaction(function () use ($templates) {
            foreach ($templates as $tmplData) {
                $stages = $tmplData['stages'];
                unset($tmplData['stages']);

                // Skip jika sudah ada template dengan nama yang sama
                $tmpl = RoutingTemplate::firstOrCreate(
                    [
                        'nama'             => $tmplData['nama'],
                        'kategori_jabatan' => $tmplData['kategori_jabatan'],
                    ],
                    array_merge($tmplData, ['aktif' => true])
                );

                // Jika stages belum ada, buat
                if ($tmpl->stages()->count() === 0) {
                    foreach ($stages as $i => $stage) {
                        RoutingTemplateStage::create(array_merge($stage, [
                            'template_id' => $tmpl->id,
                            'urutan'      => $i + 1,
                        ]));
                    }
                }
            }
        });
    }
}
