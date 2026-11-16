@extends('layouts.app')

@section('title', 'Kirim Laporan Paceklik')

@section('content')
    <a href="{{ route('rt.dashboard') }}" class="back-link">‹ Kembali ke Ringkasan</a>

    <div class="page-head">
        <div>
            <h1 class="page-title">Kirim Laporan</h1>
            <p class="page-sub">Laporkan kondisi krisis air bersih di wilayah Anda kepada BPBD.</p>
        </div>
    </div>

    {{-- PBI-07: Form ini mengirim data ke endpoint POST /rt/laporan --}}
    {{-- PBI-09: Status awal "Menunggu" ditetapkan oleh backend, bukan dari form ini --}}
    <form
        method="POST"
        action="{{ route('rt.laporan.store') }}"
        style="margin-top: 30px; max-width: 680px"
        novalidate
    >
        @csrf

        {{-- Pesan sukses --}}
        @if (session('success'))
            <div class="alert ok" style="margin-bottom: 20px">
                {{ session('success') }}
            </div>
        @endif

        {{-- Pesan error validasi global --}}
        @if ($errors->any())
            <div class="alert" style="background: var(--st-wait-bg); color: var(--st-wait-ink); margin-bottom: 20px">
                <strong>Mohon perbaiki isian berikut:</strong>
                <ul style="margin: 8px 0 0; padding-left: 18px">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card" style="padding: 28px 32px">

            {{-- Alamat --}}
            <div class="field {{ $errors->has('alamat') ? 'invalid' : '' }}">
                <label for="alamat">Alamat Lokasi Krisis <span style="color: var(--bad)">*</span></label>
                <input
                    type="text"
                    id="alamat"
                    name="alamat"
                    value="{{ old('alamat') }}"
                    placeholder="Contoh: Jl. Antapani Lama No. 12, RT 05/RW 09"
                    maxlength="255"
                    autocomplete="street-address"
                    style="width: 100%; padding: 13px 14px; border-radius: 8px; border: 1px solid var(--line); background: var(--surface); font: inherit; color: var(--ink); outline: none;"
                    onfocus="this.style.borderColor='var(--brand)'; this.style.boxShadow='0 0 0 4px rgba(47,111,214,0.12)'"
                    onblur="this.style.borderColor=''; this.style.boxShadow=''"
                >
                @error('alamat')
                    <span class="err">{{ $message }}</span>
                @enderror
            </div>

            {{-- Jumlah KK Terdampak --}}
            <div class="field {{ $errors->has('jumlah_kk_terdampak') ? 'invalid' : '' }}" style="margin-top: 18px">
                <label for="jumlah_kk_terdampak">Jumlah KK Terdampak <span style="color: var(--bad)">*</span></label>
                <input
                    type="number"
                    id="jumlah_kk_terdampak"
                    name="jumlah_kk_terdampak"
                    value="{{ old('jumlah_kk_terdampak') }}"
                    placeholder="Contoh: 42"
                    min="1"
                    max="9999"
                    style="width: 100%; padding: 13px 14px; border-radius: 8px; border: 1px solid var(--line); background: var(--surface); font: inherit; color: var(--ink); outline: none;"
                    onfocus="this.style.borderColor='var(--brand)'; this.style.boxShadow='0 0 0 4px rgba(47,111,214,0.12)'"
                    onblur="this.style.borderColor=''; this.style.boxShadow=''"
                >
                @error('jumlah_kk_terdampak')
                    <span class="err">{{ $message }}</span>
                @enderror
                <span style="font-size: 13px; color: var(--muted); margin-top: 5px; display: block">
                    Isi dengan jumlah Kepala Keluarga yang terdampak krisis air di wilayah Anda.
                </span>
            </div>

            {{-- Keterangan (opsional) --}}
            <div class="field {{ $errors->has('keterangan') ? 'invalid' : '' }}" style="margin-top: 18px">
                <label for="keterangan">Keterangan Tambahan <span style="color: var(--muted); font-weight: 400">(opsional)</span></label>
                <textarea
                    id="keterangan"
                    name="keterangan"
                    rows="4"
                    maxlength="1000"
                    placeholder="Contoh: Air mati sejak 3 hari, sumur warga sudah kering..."
                    style="width: 100%; padding: 13px 14px; border-radius: 8px; border: 1px solid var(--line); background: var(--surface); font: inherit; color: var(--ink); outline: none; resize: vertical; line-height: 1.6"
                    onfocus="this.style.borderColor='var(--brand)'; this.style.boxShadow='0 0 0 4px rgba(47,111,214,0.12)'"
                    onblur="this.style.borderColor=''; this.style.boxShadow=''"
                >{{ old('keterangan') }}</textarea>
                @error('keterangan')
                    <span class="err">{{ $message }}</span>
                @enderror
                <span style="font-size: 13px; color: var(--muted); margin-top: 5px; display: block">
                    Deskripsikan kondisi krisis air agar BPBD dapat menentukan prioritas penanganan.
                </span>
            </div>

            {{-- Informasi status awal -- ditampilkan agar pengguna paham alurnya --}}
            <div style="margin-top: 24px; padding: 14px 16px; border-radius: 8px; background: var(--st-wait-bg); border: 1px solid #f3d08a">
                <p style="margin: 0; font-size: 14px; color: var(--st-wait-ink)">
                    <strong>ℹ️ Informasi:</strong>
                    Laporan yang berhasil dikirim akan berstatus <strong>Menunggu</strong> dan segera diteruskan ke BPBD untuk diproses.
                </p>
            </div>

            {{-- Tombol aksi --}}
            <div style="margin-top: 28px; display: flex; align-items: center; gap: 12px; flex-wrap: wrap">
                <button type="submit" class="btn-primary">
                    @include('partials.icon', ['name' => 'send'])
                    Kirim Laporan
                </button>
                <a href="{{ route('rt.dashboard') }}" style="color: var(--muted); font-size: 14px; font-weight: 600; padding: 11px 4px">
                    Batal
                </a>
            </div>

        </div>
    </form>
@endsection
