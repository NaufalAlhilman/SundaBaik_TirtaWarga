<?php

namespace App\Http\Controllers\Rt;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLaporanRequest;
use App\Models\LaporanPaceklik;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LaporanController extends Controller
{
    /**
     * PBI-04: Daftar riwayat laporan milik Ketua RT yang sedang login.
     * Hanya laporan miliknya sendiri yang ditampilkan, terbaru di atas.
     */
    public function index(Request $request): View
    {
        $reports = $request->user()
            ->laporan()
            ->latest()
            ->paginate(10);

        return view('rt.laporan.index', compact('reports'));
    }

    /**
     * PBI-34: Tampilkan form pengiriman laporan baru.
     * Hanya Ketua RT yang bisa mengakses (dijaga oleh middleware EnsureRole di route).
     */
    public function create(): View
    {
        return view('rt.laporan.create');
    }

    /**
     * PBI-07: Endpoint penerima data laporan dari form Ketua RT.
     * PBI-08: Kode laporan unik (LP-YYYY-NNNN) di-generate otomatis oleh backend.
     * PBI-09: Status awal "Menunggu" (1) ditetapkan oleh backend — tidak dari input frontend.
     * PBI-10: Data tersimpan aman di dalam transaksi database.
     *
     * Alur:
     * 1. StoreLaporanRequest memvalidasi input (PBI-07).
     * 2. Transaksi DB dibuka untuk mencegah race condition pada penomoran kode.
     * 3. Kode LP-YYYY-NNNN di-generate dari jumlah laporan tahun berjalan (PBI-08).
     * 4. Status awal ditetapkan ke LaporanPaceklik::STATUS_MENUNGGU (PBI-09).
     * 5. Data disimpan ke tabel laporan_paceklik.
     * 6. Redirect ke dashboard dengan pesan sukses.
     */
    public function store(StoreLaporanRequest $request): RedirectResponse
    {
        $laporan = DB::transaction(function () use ($request) {
            // Hitung nomor urut laporan tahun ini dengan lock untuk mencegah duplikat kode
            $nomor = LaporanPaceklik::whereYear('created_at', now()->year)
                ->lockForUpdate()
                ->count() + 1;

            return $request->user()->laporan()->create([
                // Field dari input pengguna (sudah divalidasi StoreLaporanRequest)
                ...$request->validated(),

                // Field yang ditetapkan backend — TIDAK dari input pengguna
                'kode'   => sprintf('LP-%d-%04d', now()->year, $nomor),   // PBI-08
                'status' => LaporanPaceklik::STATUS_MENUNGGU,              // PBI-09
            ]);
        });

        return redirect()
            ->route('rt.dashboard')
            ->with('success', "Laporan {$laporan->kode} berhasil dikirim dan sedang menunggu verifikasi BPBD.");
    }

    /**
     * PBI-05: Detail satu laporan milik Ketua RT yang sedang login.
     * Laporan milik RT lain dianggap tidak ada (404) untuk menjaga privasi data.
     */
    public function show(Request $request, LaporanPaceklik $laporan): View
    {
        abort_unless((int) $laporan->user_id === (int) $request->user()->id, 404);

        return view('rt.laporan.show', ['report' => $laporan]);
    }
}