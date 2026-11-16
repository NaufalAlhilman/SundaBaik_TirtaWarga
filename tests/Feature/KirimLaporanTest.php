<?php

namespace Tests\Feature;

use App\Models\LaporanPaceklik;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test untuk:
 * - PBI-07: Endpoint penerima data laporan (POST /rt/laporan)
 * - PBI-09: Penetapan status awal "Menunggu" oleh backend
 * - PBI-34: Form pengiriman laporan (GET /rt/laporan/buat)
 */
class KirimLaporanTest extends TestCase
{
    use RefreshDatabase;

    // =========================================================
    // PBI-34: Halaman Form Kirim Laporan
    // =========================================================

    /**
     * PBI-34: Ketua RT dapat membuka halaman form kirim laporan.
     */
    public function test_ketua_rt_bisa_membuka_form_kirim_laporan(): void
    {
        $rt = User::factory()->create(['role' => 'rt']);

        $this->actingAs($rt)
            ->get(route('rt.laporan.create'))
            ->assertOk()
            ->assertSee('Kirim Laporan')
            ->assertSee('Alamat Lokasi Krisis')
            ->assertSee('Jumlah KK Terdampak')
            ->assertSee('Keterangan Tambahan');
    }

    /**
     * PBI-34: Tamu yang belum login diarahkan ke halaman login.
     */
    public function test_tamu_diarahkan_ke_login_saat_buka_form(): void
    {
        $this->get(route('rt.laporan.create'))
            ->assertRedirect(route('login'));
    }

    /**
     * PBI-34: Petugas BPBD tidak boleh membuka form kirim laporan RT.
     */
    public function test_bpbd_tidak_bisa_membuka_form_kirim_laporan(): void
    {
        $bpbd = User::factory()->create(['role' => 'bpbd']);

        $this->actingAs($bpbd)
            ->get(route('rt.laporan.create'))
            ->assertForbidden();
    }

    /**
     * PBI-34: Form menampilkan kembali nilai lama (old input) setelah validasi gagal.
     */
    public function test_form_menampilkan_old_input_setelah_validasi_gagal(): void
    {
        $rt = User::factory()->create(['role' => 'rt']);

        // Kirim dengan alamat valid tapi jumlah_kk_terdampak kosong
        $this->actingAs($rt)
            ->post(route('rt.laporan.store'), [
                'alamat'              => 'Jl. Test No. 1',
                'jumlah_kk_terdampak' => '', // invalid
            ])
            ->assertSessionHasErrors('jumlah_kk_terdampak');
    }

    // =========================================================
    // PBI-07: Endpoint Penerima Data Laporan
    // =========================================================

    /**
     * PBI-07: Endpoint menerima data valid dan menyimpan laporan.
     */
    public function test_laporan_tersimpan_ketika_data_valid(): void
    {
        $rt = User::factory()->create(['role' => 'rt']);

        $this->actingAs($rt)
            ->post(route('rt.laporan.store'), [
                'alamat'              => 'Jl. Antapani Lama No. 12, RT 05/RW 09',
                'jumlah_kk_terdampak' => 42,
                'keterangan'          => 'Air mati sejak 3 hari lalu.',
            ])
            ->assertRedirect(route('rt.dashboard'));

        $this->assertDatabaseHas('laporan_paceklik', [
            'user_id'             => $rt->id,
            'alamat'              => 'Jl. Antapani Lama No. 12, RT 05/RW 09',
            'jumlah_kk_terdampak' => 42,
            'keterangan'          => 'Air mati sejak 3 hari lalu.',
        ]);
    }

    /**
     * PBI-07: Endpoint menolak request tanpa alamat.
     */
    public function test_laporan_ditolak_jika_alamat_kosong(): void
    {
        $rt = User::factory()->create(['role' => 'rt']);

        $this->actingAs($rt)
            ->post(route('rt.laporan.store'), [
                'alamat'              => '',
                'jumlah_kk_terdampak' => 10,
            ])
            ->assertSessionHasErrors('alamat');
    }

    /**
     * PBI-07: Endpoint menolak jumlah_kk_terdampak = 0.
     */
    public function test_laporan_ditolak_jika_jumlah_kk_nol(): void
    {
        $rt = User::factory()->create(['role' => 'rt']);

        $this->actingAs($rt)
            ->post(route('rt.laporan.store'), [
                'alamat'              => 'Jl. Test No. 1',
                'jumlah_kk_terdampak' => 0,
            ])
            ->assertSessionHasErrors('jumlah_kk_terdampak');
    }

    /**
     * PBI-07: Endpoint menolak jumlah_kk_terdampak bukan angka.
     */
    public function test_laporan_ditolak_jika_jumlah_kk_bukan_angka(): void
    {
        $rt = User::factory()->create(['role' => 'rt']);

        $this->actingAs($rt)
            ->post(route('rt.laporan.store'), [
                'alamat'              => 'Jl. Test No. 1',
                'jumlah_kk_terdampak' => 'tiga puluh',
            ])
            ->assertSessionHasErrors('jumlah_kk_terdampak');
    }

    /**
     * PBI-07: Keterangan boleh dikosongkan (nullable).
     */
    public function test_laporan_tersimpan_tanpa_keterangan(): void
    {
        $rt = User::factory()->create(['role' => 'rt']);

        $this->actingAs($rt)
            ->post(route('rt.laporan.store'), [
                'alamat'              => 'Jl. Sukamaju No. 15',
                'jumlah_kk_terdampak' => 15,
            ])
            ->assertRedirect(route('rt.dashboard'));

        $this->assertDatabaseHas('laporan_paceklik', [
            'user_id'    => $rt->id,
            'keterangan' => null,
        ]);
    }

    /**
     * PBI-07: Petugas BPBD tidak boleh mengirim laporan.
     */
    public function test_bpbd_tidak_boleh_mengirim_laporan(): void
    {
        $bpbd = User::factory()->create(['role' => 'bpbd']);

        $this->actingAs($bpbd)
            ->post(route('rt.laporan.store'), [
                'alamat'              => 'Jl. Test',
                'jumlah_kk_terdampak' => 1,
            ])
            ->assertForbidden();
    }

    /**
     * PBI-07: Tamu tidak boleh mengirim laporan.
     */
    public function test_tamu_tidak_boleh_mengirim_laporan(): void
    {
        $this->post(route('rt.laporan.store'), [
            'alamat'              => 'Jl. Test',
            'jumlah_kk_terdampak' => 1,
        ])->assertRedirect(route('login'));
    }

    // =========================================================
    // PBI-09: Status Awal "Menunggu" Ditetapkan Backend
    // =========================================================

    /**
     * PBI-09: Status laporan baru selalu "Menunggu" (1), ditetapkan backend.
     */
    public function test_status_awal_laporan_adalah_menunggu(): void
    {
        $rt = User::factory()->create(['role' => 'rt']);

        $this->actingAs($rt)
            ->post(route('rt.laporan.store'), [
                'alamat'              => 'Jl. Antapani Lama No. 12',
                'jumlah_kk_terdampak' => 42,
            ]);

        $this->assertDatabaseHas('laporan_paceklik', [
            'user_id' => $rt->id,
            'status'  => LaporanPaceklik::STATUS_MENUNGGU, // 1
        ]);
    }

    /**
     * PBI-09: Frontend tidak bisa menetapkan status selain "Menunggu".
     * Meskipun request menyertakan field 'status', nilai itu diabaikan.
     */
    public function test_frontend_tidak_bisa_mengubah_status_awal(): void
    {
        $rt = User::factory()->create(['role' => 'rt']);

        // Simulasi frontend nakal yang mencoba mengirim status = 3 (Selesai)
        $this->actingAs($rt)
            ->post(route('rt.laporan.store'), [
                'alamat'              => 'Jl. Test Manipulasi No. 1',
                'jumlah_kk_terdampak' => 10,
                'status'              => 3, // mencoba bypass — harus diabaikan
            ]);

        // Status yang tersimpan harus tetap 1 (Menunggu), bukan 3
        $this->assertDatabaseHas('laporan_paceklik', [
            'user_id' => $rt->id,
            'status'  => LaporanPaceklik::STATUS_MENUNGGU,
        ]);

        $this->assertDatabaseMissing('laporan_paceklik', [
            'user_id' => $rt->id,
            'status'  => 3,
        ]);
    }

    /**
     * PBI-09: Kode laporan di-generate otomatis dengan format LP-YYYY-NNNN.
     */
    public function test_kode_laporan_digenerate_otomatis(): void
    {
        $rt = User::factory()->create(['role' => 'rt']);

        $this->actingAs($rt)
            ->post(route('rt.laporan.store'), [
                'alamat'              => 'Jl. Test Kode No. 1',
                'jumlah_kk_terdampak' => 5,
            ]);

        $expectedKode = sprintf('LP-%d-0001', now()->year);

        $this->assertDatabaseHas('laporan_paceklik', [
            'user_id' => $rt->id,
            'kode'    => $expectedKode,
        ]);
    }

    /**
     * PBI-09: Laporan kedua mendapat nomor urut berikutnya.
     */
    public function test_kode_laporan_berurutan_dalam_satu_tahun(): void
    {
        $rt = User::factory()->create(['role' => 'rt']);
        $payload = ['alamat' => 'Jl. Test', 'jumlah_kk_terdampak' => 5];

        $this->actingAs($rt)->post(route('rt.laporan.store'), $payload);
        $this->actingAs($rt)->post(route('rt.laporan.store'), $payload);

        $year = now()->year;
        $this->assertDatabaseHas('laporan_paceklik', ['kode' => "LP-{$year}-0001"]);
        $this->assertDatabaseHas('laporan_paceklik', ['kode' => "LP-{$year}-0002"]);
    }
}
