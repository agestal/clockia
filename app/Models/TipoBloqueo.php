<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\TipoBloqueo
 *
 * @property int $id
 * @property string $nombre
 * @property string|null $descripcion
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Collection<int, Bloqueo> $bloqueos
 */
class TipoBloqueo extends Model
{
    use HasFactory;

    protected $table = 'tipos_bloqueo';

    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    public function bloqueos(): HasMany
    {
        return $this->hasMany(Bloqueo::class);
    }
}
