<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreEncuestaPlantillaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $preguntas = collect($this->input('preguntas', []))
            ->map(function ($pregunta) {
                $id = trim((string) data_get($pregunta, 'id', ''));
                $etiqueta = preg_replace('/\s+/u', ' ', trim((string) data_get($pregunta, 'etiqueta', '')));
                $descripcion = trim((string) data_get($pregunta, 'descripcion', ''));

                return [
                    'id' => $id !== '' ? (int) $id : null,
                    'etiqueta' => $etiqueta !== '' ? $etiqueta : null,
                    'descripcion' => $descripcion !== '' ? $descripcion : null,
                    'activo' => $this->normalizeBoolean(data_get($pregunta, 'activo')),
                ];
            })
            ->filter(static fn (array $pregunta) => $pregunta['id'] !== null || $pregunta['etiqueta'] !== null || $pregunta['descripcion'] !== null)
            ->values()
            ->all();

        $predeterminada = $this->normalizeBoolean($this->input('predeterminada'));

        $this->merge([
            'nombre' => preg_replace('/\s+/u', ' ', trim((string) $this->input('nombre', ''))),
            'descripcion' => trim((string) $this->input('descripcion', '')) ?: null,
            'activo' => $predeterminada ? true : $this->normalizeBoolean($this->input('activo')),
            'predeterminada' => $predeterminada,
            'permite_comentario_final' => $this->normalizeBoolean($this->input('permite_comentario_final')),
            'comentario_placeholder' => trim((string) $this->input('comentario_placeholder', '')) ?: null,
            'titulo_publico' => trim((string) $this->input('titulo_publico', '')) ?: null,
            'intro_publica' => trim((string) $this->input('intro_publica', '')) ?: null,
            'agradecimiento_titulo' => trim((string) $this->input('agradecimiento_titulo', '')) ?: null,
            'agradecimiento_texto' => trim((string) $this->input('agradecimiento_texto', '')) ?: null,
            'preguntas' => $preguntas,
        ]);
    }

    public function rules(): array
    {
        return [
            'negocio_id' => ['required', 'integer', 'exists:negocios,id'],
            'nombre' => ['required', 'string', 'min:2', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'activo' => ['required', 'boolean'],
            'predeterminada' => ['required', 'boolean'],
            'escala_min' => ['required', 'integer', 'min:0', 'max:9'],
            'escala_max' => ['required', 'integer', 'min:1', 'max:10'],
            'permite_comentario_final' => ['required', 'boolean'],
            'comentario_placeholder' => ['nullable', 'string', 'max:255'],
            'titulo_publico' => ['nullable', 'string', 'max:255'],
            'intro_publica' => ['nullable', 'string'],
            'agradecimiento_titulo' => ['nullable', 'string', 'max:255'],
            'agradecimiento_texto' => ['nullable', 'string'],
            'preguntas' => ['required', 'array', 'min:1'],
            'preguntas.*.id' => ['nullable', 'integer', 'exists:encuesta_items,id'],
            'preguntas.*.etiqueta' => ['required', 'string', 'min:2', 'max:255'],
            'preguntas.*.descripcion' => ['nullable', 'string'],
            'preguntas.*.activo' => ['required', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $escalaMin = (int) $this->input('escala_min');
            $escalaMax = (int) $this->input('escala_max');

            if ($escalaMax <= $escalaMin) {
                $validator->errors()->add('escala_max', 'La escala maxima debe ser mayor que la minima.');
            }

            $hayPreguntaActiva = collect($this->input('preguntas', []))
                ->contains(static fn (array $pregunta) => (bool) ($pregunta['activo'] ?? false));

            if (! $hayPreguntaActiva) {
                $validator->errors()->add('preguntas', 'Debes dejar al menos una pregunta activa.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'negocio_id.required' => 'Debes seleccionar un negocio.',
            'negocio_id.exists' => 'El negocio seleccionado no existe.',
            'nombre.required' => 'El nombre de la encuesta es obligatorio.',
            'nombre.min' => 'El nombre debe tener al menos 2 caracteres.',
            'comentario_placeholder.max' => 'El placeholder del comentario no puede superar los 255 caracteres.',
            'titulo_publico.max' => 'El titulo publico no puede superar los 255 caracteres.',
            'agradecimiento_titulo.max' => 'El titulo de agradecimiento no puede superar los 255 caracteres.',
            'preguntas.required' => 'Debes añadir al menos una pregunta.',
            'preguntas.min' => 'Debes añadir al menos una pregunta.',
            'preguntas.*.etiqueta.required' => 'Cada pregunta debe tener un enunciado.',
            'preguntas.*.etiqueta.min' => 'Cada pregunta debe tener al menos 2 caracteres.',
            'preguntas.*.etiqueta.max' => 'Cada pregunta no puede superar los 255 caracteres.',
        ];
    }

    private function normalizeBoolean(mixed $value): bool
    {
        return in_array($value, [true, 1, '1', 'true', 'on', 'yes'], true);
    }
}
