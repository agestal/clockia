<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EncuestaPlantilla extends Model
{
    use HasFactory;

    protected $table = 'encuesta_plantillas';

    protected $fillable = [
        'negocio_id',
        'nombre',
        'descripcion',
        'activo',
        'predeterminada',
        'escala_min',
        'escala_max',
        'permite_comentario_final',
        'comentario_placeholder',
        'titulo_publico',
        'intro_publica',
        'agradecimiento_titulo',
        'agradecimiento_texto',
    ];

    protected function casts(): array
    {
        return [
            'negocio_id' => 'integer',
            'activo' => 'boolean',
            'predeterminada' => 'boolean',
            'escala_min' => 'integer',
            'escala_max' => 'integer',
            'permite_comentario_final' => 'boolean',
        ];
    }

    public static function ensureDefaultForBusiness(Negocio $negocio): self
    {
        $template = self::query()
            ->where('negocio_id', $negocio->id)
            ->orderByDesc('predeterminada')
            ->orderBy('id')
            ->first();

        if (! $template) {
            $template = self::create([
                'negocio_id' => $negocio->id,
                'nombre' => 'Encuesta post-experiencia',
                'descripcion' => 'Plantilla inicial para el feedback tras la reserva.',
                'activo' => true,
                'predeterminada' => true,
                'escala_min' => 0,
                'escala_max' => 10,
                'permite_comentario_final' => true,
                'comentario_placeholder' => 'Si quieres, dejanos algun comentario adicional.',
                'titulo_publico' => 'Comparte tu valoracion',
                'intro_publica' => 'Nos ayuda mucho saber como ha ido la experiencia.',
                'agradecimiento_titulo' => 'Gracias por tu valoracion',
                'agradecimiento_texto' => 'Tu opinion nos ayuda a seguir mejorando.',
            ]);
        }

        if (! $template->activo) {
            $template->update(['activo' => true]);
        }

        $hasDefault = self::query()
            ->where('negocio_id', $negocio->id)
            ->where('predeterminada', true)
            ->exists();

        if (! $hasDefault) {
            $template->update(['predeterminada' => true]);
        }

        if (! $template->preguntas()->where('activo', true)->exists()) {
            EncuestaItem::create([
                'negocio_id' => $negocio->id,
                'encuesta_plantilla_id' => $template->id,
                'clave' => "tpl_{$template->id}_1_valoracion_general",
                'etiqueta' => 'Valoracion general de la experiencia',
                'descripcion' => '¿Como valorarias tu visita en general?',
                'orden' => 1,
                'activo' => true,
            ]);
        }

        return $template->fresh(['preguntas']);
    }

    public static function defaultForBusiness(Negocio $negocio): self
    {
        $template = self::query()
            ->where('negocio_id', $negocio->id)
            ->where('activo', true)
            ->orderByDesc('predeterminada')
            ->orderBy('id')
            ->first();

        if ($template && $template->preguntas()->where('activo', true)->exists()) {
            return $template;
        }

        return self::ensureDefaultForBusiness($negocio);
    }

    public function negocio(): BelongsTo
    {
        return $this->belongsTo(Negocio::class);
    }

    public function preguntas(): HasMany
    {
        return $this->hasMany(EncuestaItem::class, 'encuesta_plantilla_id')->orderBy('orden');
    }

    public function encuestas(): HasMany
    {
        return $this->hasMany(Encuesta::class, 'encuesta_plantilla_id');
    }

    public function buildSnapshot(): array
    {
        $preguntas = $this->preguntas()
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
            'titulo_publico' => $this->titulo_publico ?: 'Comparte tu valoracion',
            'intro_publica' => $this->intro_publica ?: 'Nos ayuda mucho saber como ha ido la experiencia.',
            'agradecimiento_titulo' => $this->agradecimiento_titulo ?: 'Gracias por tu valoracion',
            'agradecimiento_texto' => $this->agradecimiento_texto ?: 'Tu opinion nos ayuda a seguir mejorando.',
            'permite_comentario_final' => $this->permite_comentario_final,
            'comentario_placeholder' => $this->comentario_placeholder ?: 'Si quieres, dejanos algun comentario adicional.',
            'escala_min' => $this->escala_min,
            'escala_max' => $this->escala_max,
            'preguntas' => $preguntas,
        ];
    }
}
