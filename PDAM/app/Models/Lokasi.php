<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $nama_lokasi
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Lokasi extends Model
{
    protected $fillable = [
        'nama_lokasi',
        'latitude',
        'longitude',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    /**
     * Aset valve yang berada di lokasi ini.
     *
     * @return HasMany<AsetValve, $this>
     */
    public function asetValves(): HasMany
    {
        return $this->hasMany(AsetValve::class);
    }

    /**
     * Log tekanan yang tercatat di lokasi ini.
     *
     * @return HasMany<LogTekanan, $this>
     */
    public function logTekanans(): HasMany
    {
        return $this->hasMany(LogTekanan::class);
    }

    /**
     * Log tekanan terbaru di lokasi ini.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function latestLogTekanan()
    {
        return $this->hasOne(LogTekanan::class)->latestOfMany('waktu_pengecekan');
    }
}
