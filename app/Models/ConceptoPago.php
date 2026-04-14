<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\ConceptoPago
 *
 * @property int $id
 * @property string $nombre
 * @property string|null $descripcion
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Collection<int, Pago> $pagos
 */
class ConceptoPago extends Model
{
    use HasFactory;

    protected $table = 'conceptos_pago';

    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class);
    }
}
