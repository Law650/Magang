<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $aset_valve_id
 * @property int|null $user_id
 * @property string $nama_teknisi
 * @property \Illuminate\Support\Carbon $waktu_kegiatan
 * @property string $aksi_kerja
 * @property float $jumlah_putaran
 * @property string|null $keterangan
 * @property float $snapshot_sisa_bukaan
 * @property float $snapshot_total_tutupan
 * @property float|null $latitude
 * @property float|null $longitude
 * @property string|null $foto_eviden
 * @property string|null $foto_eviden_2
 * @property bool $is_edited
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class LogValve extends Model
{
    protected $fillable = [
        'aset_valve_id',
        'user_id',
        'nama_teknisi',
        'waktu_kegiatan',
        'aksi_kerja',
        'jumlah_putaran',
        'keterangan',
        'snapshot_sisa_bukaan',
        'snapshot_total_tutupan',
        'latitude',
        'longitude',
        'foto_eviden',
        'foto_eviden_2',
        'is_edited',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'waktu_kegiatan' => 'datetime',
            'jumlah_putaran' => 'decimal:2',
            'snapshot_sisa_bukaan' => 'decimal:2',
            'snapshot_total_tutupan' => 'decimal:2',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
        ];
    }

    /**
     * Aset valve terkait.
     * withDefault agar tidak fatal error jika aset sudah dihapus (soft delete fallback).
     *
     * @return BelongsTo<AsetValve, $this>
     */
    public function asetValve(): BelongsTo
    {
        return $this->belongsTo(AsetValve::class)->withDefault([
            'nama_aset' => 'Aset Tidak Ditemukan',
            'kapasitas_full_putaran' => 0,
        ]);
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

    /**
     * Menghitung jarak antara koordinat log dengan koordinat aset master (dalam satuan Meter).
     * Menggunakan metode Haversine.
     *
     * @return float|null
     */
    public function getJarakDariMasterAttribute(): ?float
    {
        if (!$this->latitude || !$this->longitude || 
            !$this->asetValve || !$this->asetValve->lokasi || 
            !$this->asetValve->lokasi->latitude || !$this->asetValve->lokasi->longitude) {
            return null;
        }

        $lat1 = (float) $this->latitude;
        $lon1 = (float) $this->longitude;
        $lat2 = (float) $this->asetValve->lokasi->latitude;
        $lon2 = (float) $this->asetValve->lokasi->longitude;

        $earthRadius = 6371000; // Radius bumi dalam meter

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c; // Jarak dalam meter
    }


}
