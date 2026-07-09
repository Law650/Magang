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
            'latitude' => 'decimal:6',
            'longitude' => 'decimal:6',
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
}
