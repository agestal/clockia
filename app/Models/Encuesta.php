<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Encuesta extends Model
{
    use HasFactory;

    protected $table = 'encuestas';

    protected $fillable = [
        'reserva_id',
        'negocio_id',
        'token',
        'enviada_en',
        'respondida_en',
        'comentario_general',
    ];

    protected function casts(): array
    {
        return [
            'reserva_id' => 'integer',
            'negocio_id' => 'integer',
            'enviada_en' => 'datetime',
            'respondida_en' => 'datetime',
        ];
    }

    public static function generarToken(): string
    {
        return Str::random(64);
    }

    public function estaRespondida(): bool
    {
        return $this->respondida_en !== null;
    }

    public function reserva(): BelongsTo
    {
        return $this->belongsTo(Reserva::class);
    }

    public function negocio(): BelongsTo
    {
        return $this->belongsTo(Negocio::class);
    }

    public function respuestas(): HasMany
    {
        return $this->hasMany(EncuestaRespuesta::class);
    }
}
