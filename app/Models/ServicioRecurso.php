<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\ServicioRecurso
 *
 * @property int $id
 * @property int $servicio_id
 * @property int $recurso_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Servicio $servicio
 * @property-read Recurso $recurso
 */
class ServicioRecurso extends Model
{
    use HasFactory;

    protected $table = 'servicio_recurso';

    protected $fillable = [
        'servicio_id',
        'recurso_id',
    ];

    protected function casts(): array
    {
        return [
            'servicio_id' => 'integer',
            'recurso_id' => 'integer',
        ];
    }

    public function servicio(): BelongsTo
    {
        return $this->belongsTo(Servicio::class);
    }

    public function recurso(): BelongsTo
    {
        return $this->belongsTo(Recurso::class);
    }
}
