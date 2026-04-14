<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\TipoPrecio
 *
 * @property int $id
 * @property string $nombre
 * @property string|null $descripcion
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Collection<int, Servicio> $servicios
 */
class TipoPrecio extends Model
{
    use HasFactory;

    protected $table = 'tipos_precio';

    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    public function servicios(): HasMany
    {
        return $this->hasMany(Servicio::class);
    }
}
