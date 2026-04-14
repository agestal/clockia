<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\RecursoCombinacion
 *
 * @property int $id
 * @property int $recurso_id
 * @property int $recurso_combinado_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Recurso $recurso
 * @property-read Recurso $recursoCombinado
 */
class RecursoCombinacion extends Model
{
    use HasFactory;

    protected $table = 'recurso_combinaciones';

    protected $fillable = [
        'recurso_id',
        'recurso_combinado_id',
    ];

    protected function casts(): array
    {
        return [
            'recurso_id' => 'integer',
            'recurso_combinado_id' => 'integer',
        ];
    }

    public function recurso(): BelongsTo
    {
        return $this->belongsTo(Recurso::class);
    }

    public function recursoCombinado(): BelongsTo
    {
        return $this->belongsTo(Recurso::class, 'recurso_combinado_id');
    }
}
