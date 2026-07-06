<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $lokasi_id
 * @property string $nama_aset
 * @property float $kapasitas_full_putaran
 * @property float $total_tutupan_saat_ini
 * @property float $sisa_bukaan
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class AsetValve extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'lokasi_id',
        'nama_aset',
        'kapasitas_full_putaran',
        'total_tutupan_saat_ini',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'kapasitas_full_putaran' => 'decimal:2',
            'total_tutupan_saat_ini' => 'decimal:2',
        ];
    }

    /**
     * Sisa bukaan valve = kapasitas full - total tutupan.
     * Diimplementasikan sebagai accessor agar konsisten di seluruh sistem.
     */
    protected function sisaBukaan(): Attribute
    {
        return Attribute::get(
            fn (): float => round((float) $this->kapasitas_full_putaran - (float) $this->total_tutupan_saat_ini, 2)
        );
    }

    /**
     * Persentase sisa bukaan untuk progress bar visual.
     */
    protected function persentaseBukaan(): Attribute
    {
        return Attribute::get(function (): float {
            if ((float) $this->kapasitas_full_putaran <= 0) {
                return 0;
            }

            return round(($this->sisa_bukaan / (float) $this->kapasitas_full_putaran) * 100, 1);
        });
    }

    /**
     * Lokasi tempat aset ini berada.
     *
     * @return BelongsTo<Lokasi, $this>
     */
    public function lokasi(): BelongsTo
    {
        return $this->belongsTo(Lokasi::class);
    }

    /**
     * Riwayat log aktivitas valve ini.
     *
     * @return HasMany<LogValve, $this>
     */
    public function logValves(): HasMany
    {
        return $this->hasMany(LogValve::class);
    }
}
