<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlantillaEmail extends Model
{
    use HasFactory;

    public const TIPO_CONFIRMACION = 'confirmacion';
    public const TIPO_RECORDATORIO = 'recordatorio';
    public const TIPO_ENCUESTA = 'encuesta';

    protected $table = 'plantillas_email';

    protected $fillable = [
        'negocio_id',
        'tipo',
        'asunto',
        'titulo',
        'saludo',
        'introduccion',
        'cuerpo',
        'texto_boton',
        'texto_pie',
        'color_primario',
        'color_boton',
        'color_fondo',
        'color_texto',
    ];

    protected function casts(): array
    {
        return [
            'negocio_id' => 'integer',
        ];
    }

    public static function tipos(): array
    {
        return [
            self::TIPO_CONFIRMACION,
            self::TIPO_RECORDATORIO,
            self::TIPO_ENCUESTA,
        ];
    }

    public static function etiquetas(): array
    {
        return [
            self::TIPO_CONFIRMACION => 'Confirmacion',
            self::TIPO_RECORDATORIO => 'Recordatorio',
            self::TIPO_ENCUESTA => 'Encuesta',
        ];
    }

    public static function defaultsFor(string $tipo): array
    {
        return match ($tipo) {
            self::TIPO_CONFIRMACION => [
                'asunto' => 'Confirmacion de reserva - {{negocio}}',
                'titulo' => 'Reserva confirmada',
                'saludo' => 'Hola{{nombre_fragmento}}',
                'introduccion' => 'Tu reserva ha quedado confirmada.',
                'cuerpo' => "Te esperamos el {{fecha}} a las {{hora}} para {{servicio}}.\nEn este email tienes los datos principales de tu reserva.",
                'texto_boton' => null,
                'texto_pie' => 'Este email ha sido enviado automaticamente por {{negocio}}.',
                'color_primario' => '#7B3F00',
                'color_boton' => '#7B3F00',
                'color_fondo' => '#F5F2EE',
                'color_texto' => '#2C241D',
            ],
            self::TIPO_RECORDATORIO => [
                'asunto' => 'Recordatorio de reserva - {{negocio}}',
                'titulo' => 'Tu reserva se acerca',
                'saludo' => 'Hola{{nombre_fragmento}}',
                'introduccion' => 'Queremos recordarte tu proxima reserva.',
                'cuerpo' => "Tu experiencia {{servicio}} esta prevista para el {{fecha}} a las {{hora}}.\nSi necesitas revisar algun detalle, contacta con {{negocio}}.",
                'texto_boton' => null,
                'texto_pie' => '{{negocio}} · Recordatorio automatico',
                'color_primario' => '#A85C00',
                'color_boton' => '#A85C00',
                'color_fondo' => '#F7F3EC',
                'color_texto' => '#2C241D',
            ],
            self::TIPO_ENCUESTA => [
                'asunto' => 'Queremos saber como fue tu experiencia - {{negocio}}',
                'titulo' => 'Comparte tu valoracion',
                'saludo' => 'Hola{{nombre_fragmento}}',
                'introduccion' => 'Gracias por visitarnos.',
                'cuerpo' => "Nos encantaria conocer tu opinion sobre {{servicio}} del {{fecha}}.\nLa encuesta solo te llevara unos segundos.",
                'texto_boton' => 'Responder encuesta',
                'texto_pie' => '{{negocio}} · Encuesta de satisfaccion',
                'color_primario' => '#4E8B31',
                'color_boton' => '#2E7D32',
                'color_fondo' => '#F3F7F1',
                'color_texto' => '#213025',
            ],
            default => [
                'asunto' => 'Comunicacion de {{negocio}}',
                'titulo' => 'Actualizacion de tu reserva',
                'saludo' => 'Hola{{nombre_fragmento}}',
                'introduccion' => null,
                'cuerpo' => null,
                'texto_boton' => null,
                'texto_pie' => '{{negocio}}',
                'color_primario' => '#7B3F00',
                'color_boton' => '#7B3F00',
                'color_fondo' => '#F5F2EE',
                'color_texto' => '#2C241D',
            ],
        };
    }

    public static function ensureDefaultsForBusiness(Negocio $negocio): void
    {
        foreach (self::tipos() as $tipo) {
            self::query()->firstOrCreate(
                [
                    'negocio_id' => $negocio->id,
                    'tipo' => $tipo,
                ],
                []
            );
        }
    }

    public function negocio(): BelongsTo
    {
        return $this->belongsTo(Negocio::class);
    }

    public function etiquetaTipo(): string
    {
        return self::etiquetas()[$this->tipo] ?? ucfirst($this->tipo);
    }

    public function resolved(array $variables = []): array
    {
        $defaults = self::defaultsFor($this->tipo);
        $stored = [
            'asunto' => $this->asunto,
            'titulo' => $this->titulo,
            'saludo' => $this->saludo,
            'introduccion' => $this->introduccion,
            'cuerpo' => $this->cuerpo,
            'texto_boton' => $this->texto_boton,
            'texto_pie' => $this->texto_pie,
            'color_primario' => $this->color_primario,
            'color_boton' => $this->color_boton,
            'color_fondo' => $this->color_fondo,
            'color_texto' => $this->color_texto,
        ];

        $resolved = array_replace($defaults, array_filter($stored, static fn ($value) => $value !== null && $value !== ''));

        foreach (['asunto', 'titulo', 'saludo', 'introduccion', 'cuerpo', 'texto_boton', 'texto_pie'] as $key) {
            $resolved[$key] = self::replacePlaceholders($resolved[$key] ?? null, $variables);
        }

        return $resolved;
    }

    private static function replacePlaceholders(?string $value, array $variables): ?string
    {
        if ($value === null || $value === '') {
            return $value;
        }

        $replacements = [];

        foreach ($variables as $key => $replacement) {
            $replacements['{{'.$key.'}}'] = (string) ($replacement ?? '');
        }

        return trim(strtr($value, $replacements));
    }
}
