<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\EstadoReserva
 *
 * @property int $id
 * @property string $nombre
 * @property string|null $descripcion
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Collection<int, Reserva> $reservas
 */
class EstadoReserva extends Model
{
    use HasFactory;

    protected $table = 'estados_reserva';

    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    public function reservas(): HasMany
    {
        return $this->hasMany(Reserva::class);
    }
}
