<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $jenis 'tekanan' atau 'valve'
 * @property string $nama_lokasi (Unified Display Name) Jika tekanan: gabungan SR & Nama. Jika valve: nama valvenya.
 * @property string|null $no_sr Hanya untuk jenis 'tekanan'
 * @property string|null $nama_pelanggan Hanya untuk jenis 'tekanan'
 * @property string|null $alamat
 * @property string|null $desa
 * @property float|null $latitude
 * @property float|null $longitude
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Lokasi extends Model
{
    // Catatan Developer: 
    // Kolom 'nama_lokasi' dipertahankan sebagai "Unified Display Name" agar Frontend (Mobile App / Web)
    // bisa langsung menampilkan 1 variabel tanpa perlu merakit string secara manual, yang mempercepat performa API.
    
    protected $fillable = [
        'no_sr',
        'nama_pelanggan',
        'alamat',
        'desa',
        'nama_lokasi',
        'latitude',
        'longitude',
        'jenis',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
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
