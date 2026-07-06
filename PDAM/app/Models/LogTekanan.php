<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $lokasi_id
 * @property float $nilai_tekanan
 * @property string $status
 * @property \Illuminate\Support\Carbon $waktu_pengecekan
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class LogTekanan extends Model
{
    protected $fillable = [
        'lokasi_id',
        'nilai_tekanan',
        'status',
        'waktu_pengecekan',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'waktu_pengecekan' => 'datetime',
            'nilai_tekanan' => 'decimal:2',
        ];
    }

    /**
     * Klasifikasi status tekanan otomatis berdasarkan nilai.
     * Normal ≥ 1.0, Rendah 0.5–<1.0, Kritis < 0.5
     */
    public static function klasifikasiStatus(float $nilaiTekanan): string
    {
        if ($nilaiTekanan >= 1.0) {
            return 'normal';
        }

        if ($nilaiTekanan >= 0.5) {
            return 'rendah';
        }

        return 'kritis';
    }

    /**
     * Lokasi tempat pengecekan tekanan dilakukan.
     *
     * @return BelongsTo<Lokasi, $this>
     */
    public function lokasi(): BelongsTo
    {
        return $this->belongsTo(Lokasi::class);
    }
}
