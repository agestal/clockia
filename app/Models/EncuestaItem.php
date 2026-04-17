<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EncuestaItem extends Model
{
    use HasFactory;

    protected $table = 'encuesta_items';

    protected $fillable = [
        'negocio_id',
        'encuesta_plantilla_id',
        'clave',
        'etiqueta',
        'descripcion',
        'orden',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'negocio_id' => 'integer',
            'encuesta_plantilla_id' => 'integer',
            'orden' => 'integer',
            'activo' => 'boolean',
        ];
    }

    public function negocio(): BelongsTo
    {
        return $this->belongsTo(Negocio::class);
    }

    public function plantilla(): BelongsTo
    {
        return $this->belongsTo(EncuestaPlantilla::class, 'encuesta_plantilla_id');
    }
}
