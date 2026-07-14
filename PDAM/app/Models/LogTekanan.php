<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $lokasi_id
 * @property int|null $user_id
 * @property string|null $nama_teknisi
 * @property float $nilai_tekanan
 * @property string $status
 * @property \Illuminate\Support\Carbon $waktu_pengecekan
 * @property float|null $latitude
 * @property float|null $longitude
 * @property string|null $foto_eviden
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class LogTekanan extends Model
{
    protected $fillable = [
        'lokasi_id',
        'user_id',
        'nama_teknisi',
        'nilai_tekanan',
        'status',
        'status_aliran',
        'kekeruhan',
        'keterangan',
        'waktu_pengecekan',
        'latitude',
        'longitude',
        'foto_eviden',
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
            'latitude' => 'decimal:6',
            'longitude' => 'decimal:6',
        ];
    }

    /**
     * Klasifikasi status tekanan otomatis berdasarkan nilai.
     * Normal >= 0.7, Rendah > 0 - < 0.7, Kritis <= 0
     */
    public static function klasifikasiStatus(float $nilaiTekanan): string
    {
        if ($nilaiTekanan >= 0.7) {
            return 'normal';
        }

        if ($nilaiTekanan > 0) {
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

    /**
     * User/teknisi yang mencatat log ini.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withDefault([
            'name' => 'Teknisi Tidak Diketahui',
        ]);
    }
}
