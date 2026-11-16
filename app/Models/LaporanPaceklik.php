<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class LaporanPaceklik extends Model
{
    use HasFactory;

    protected $table = 'laporan_paceklik';

    /**
     * Kolom yang boleh diisi secara massal.
     * Sesuai dengan migration aktif: 2026_11_07_..._create_laporan_paceklik_table.php
     * Status ditentukan oleh backend (PBI-09), bukan dari input pengguna.
     */
    protected $fillable = [
        'kode',                 // LP-YYYY-NNNN — di-generate otomatis oleh Controller (PBI-08)
        'user_id',              // FK ke tabel users (Ketua RT pelapor)
        'alamat',               // Alamat lokasi krisis air
        'jumlah_kk_terdampak',  // Jumlah KK terdampak (integer ≥ 1)
        'keterangan',           // Keterangan tambahan (nullable)
        'status',               // 1=Menunggu, 2=Diverifikasi, 3=Selesai (PBI-09)
        'diverifikasi_at',      // Waktu verifikasi BPBD (nullable)
        'selesai_at',           // Waktu penanganan selesai (nullable)
    ];

    /**
     * Cast kolom ke tipe PHP yang tepat.
     */
    protected $casts = [
        'jumlah_kk_terdampak' => 'integer',
        'status'              => 'integer',
        'diverifikasi_at'     => 'datetime',
        'selesai_at'          => 'datetime',
    ];

    /**
     * Label teks untuk nilai status integer.
     * Gunakan konstanta ini di Controller/View agar tidak ada magic number.
     */
    public const STATUS_MENUNGGU    = 1;
    public const STATUS_DIVERIFIKASI = 2;
    public const STATUS_SELESAI     = 3;

    public const STATUS_LABELS = [
        self::STATUS_MENUNGGU     => 'Menunggu',
        self::STATUS_DIVERIFIKASI => 'Diverifikasi',
        self::STATUS_SELESAI      => 'Selesai',
    ];

    /** Ketua RT pemilik laporan. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Semua penetapan bantuan terkait laporan ini. */
    public function penetapanBantuan(): HasMany
    {
        return $this->hasMany(PenetapanBantuan::class, 'laporan_id');
    }

    /** Penetapan bantuan yang sedang aktif. */
    public function penetapanAktif(): HasOne
    {
        return $this->hasOne(PenetapanBantuan::class, 'laporan_id')->where('status_bantuan', 'Aktif');
    }
}