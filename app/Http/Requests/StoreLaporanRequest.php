<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * PBI-07: Validasi backend untuk data laporan yang dikirim Ketua RT.
 * Status awal TIDAK termasuk field yang diterima dari frontend (PBI-09).
 */
class StoreLaporanRequest extends FormRequest
{
    /**
     * Otorisasi ditangani oleh middleware EnsureRole di route group.
     * FormRequest ini hanya bertugas memvalidasi data, bukan membatasi akses.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi data laporan.
     *
     * PBI-07: Backend memvalidasi semua field sebelum disimpan.
     * PBI-09: Field 'status' tidak ada di sini — status awal ditetapkan
     *         oleh Controller, bukan dari input pengguna.
     */
    public function rules(): array
    {
        return [
            'alamat'              => ['required', 'string', 'max:255'],
            'jumlah_kk_terdampak' => ['required', 'integer', 'min:1', 'max:9999'],
            'keterangan'          => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Pesan error dalam Bahasa Indonesia agar mudah dipahami pengguna.
     */
    public function messages(): array
    {
        return [
            'alamat.required'              => 'Alamat lokasi wajib diisi.',
            'alamat.max'                   => 'Alamat maksimal 255 karakter.',
            'jumlah_kk_terdampak.required' => 'Jumlah KK terdampak wajib diisi.',
            'jumlah_kk_terdampak.integer'  => 'Jumlah KK terdampak harus berupa angka bulat.',
            'jumlah_kk_terdampak.min'      => 'Jumlah KK terdampak minimal 1.',
            'jumlah_kk_terdampak.max'      => 'Jumlah KK terdampak maksimal 9.999.',
            'keterangan.max'               => 'Keterangan maksimal 1.000 karakter.',
        ];
    }
}
