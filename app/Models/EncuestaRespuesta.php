<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EncuestaRespuesta extends Model
{
    use HasFactory;

    protected $table = 'encuesta_respuestas';

    protected $fillable = [
        'encuesta_id',
        'encuesta_item_id',
        'puntuacion',
    ];

    protected function casts(): array
    {
        return [
            'encuesta_id' => 'integer',
            'encuesta_item_id' => 'integer',
            'puntuacion' => 'integer',
        ];
    }

    public function encuesta(): BelongsTo
    {
        return $this->belongsTo(Encuesta::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(EncuestaItem::class, 'encuesta_item_id');
    }
}
