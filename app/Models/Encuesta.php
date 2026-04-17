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
        'encuesta_plantilla_id',
        'token',
        'activo',
        'enviada_en',
        'respondida_en',
        'comentario_general',
        'contenido_snapshot',
    ];

    protected function casts(): array
    {
        return [
            'reserva_id' => 'integer',
            'negocio_id' => 'integer',
            'encuesta_plantilla_id' => 'integer',
            'activo' => 'boolean',
            'enviada_en' => 'datetime',
            'respondida_en' => 'datetime',
            'contenido_snapshot' => 'array',
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

    public function estaActiva(): bool
    {
        return $this->activo && ! $this->estaRespondida();
    }

    public function puedeResponder(): bool
    {
        return $this->estaActiva() && count($this->contenidoEncuesta()['preguntas'] ?? []) > 0;
    }

    public function contenidoEncuesta(): array
    {
        if (is_array($this->contenido_snapshot) && ($this->contenido_snapshot['preguntas'] ?? []) !== []) {
            return $this->contenido_snapshot;
        }

        $plantilla = $this->relationLoaded('plantilla') ? $this->plantilla : $this->plantilla()->with('preguntas')->first();

        if ($plantilla) {
            return $plantilla->buildSnapshot();
        }

        $preguntas = EncuestaItem::query()
            ->where('negocio_id', $this->negocio_id)
            ->where('activo', true)
            ->orderBy('orden')
            ->get()
            ->map(static fn (EncuestaItem $pregunta) => [
                'id' => $pregunta->id,
                'etiqueta' => $pregunta->etiqueta,
                'descripcion' => $pregunta->descripcion,
                'orden' => $pregunta->orden,
            ])
            ->values()
            ->all();

        return [
            'titulo_publico' => 'Comparte tu valoracion',
            'intro_publica' => 'Nos ayuda mucho saber como ha ido la experiencia.',
            'agradecimiento_titulo' => 'Gracias por tu valoracion',
            'agradecimiento_texto' => 'Tu opinion nos ayuda a seguir mejorando.',
            'permite_comentario_final' => true,
            'comentario_placeholder' => 'Si quieres, dejanos algun comentario adicional.',
            'escala_min' => 0,
            'escala_max' => 10,
            'preguntas' => $preguntas,
        ];
    }

    public function reserva(): BelongsTo
    {
        return $this->belongsTo(Reserva::class);
    }

    public function negocio(): BelongsTo
    {
        return $this->belongsTo(Negocio::class);
    }

    public function plantilla(): BelongsTo
    {
        return $this->belongsTo(EncuestaPlantilla::class, 'encuesta_plantilla_id');
    }

    public function respuestas(): HasMany
    {
        return $this->hasMany(EncuestaRespuesta::class);
    }
}
