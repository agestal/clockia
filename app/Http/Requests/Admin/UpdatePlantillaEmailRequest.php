<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePlantillaEmailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $fields = [
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

        $normalized = [];

        foreach ($fields as $field) {
            $value = trim((string) $this->input($field, ''));
            $normalized[$field] = $value !== '' ? $value : null;
        }

        $this->merge($normalized);
    }

    public function rules(): array
    {
        return [
            'asunto' => ['nullable', 'string', 'max:255'],
            'titulo' => ['nullable', 'string', 'max:255'],
            'saludo' => ['nullable', 'string', 'max:255'],
            'introduccion' => ['nullable', 'string'],
            'cuerpo' => ['nullable', 'string'],
            'texto_boton' => ['nullable', 'string', 'max:255'],
            'texto_pie' => ['nullable', 'string'],
            'color_primario' => ['nullable', 'regex:/^#(?:[0-9A-Fa-f]{3}){1,2}$/'],
            'color_boton' => ['nullable', 'regex:/^#(?:[0-9A-Fa-f]{3}){1,2}$/'],
            'color_fondo' => ['nullable', 'regex:/^#(?:[0-9A-Fa-f]{3}){1,2}$/'],
            'color_texto' => ['nullable', 'regex:/^#(?:[0-9A-Fa-f]{3}){1,2}$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'asunto.max' => 'El asunto no puede superar los 255 caracteres.',
            'titulo.max' => 'El titulo no puede superar los 255 caracteres.',
            'saludo.max' => 'El saludo no puede superar los 255 caracteres.',
            'texto_boton.max' => 'El texto del boton no puede superar los 255 caracteres.',
            'color_primario.regex' => 'El color primario debe estar en formato HEX.',
            'color_boton.regex' => 'El color del boton debe estar en formato HEX.',
            'color_fondo.regex' => 'El color de fondo debe estar en formato HEX.',
            'color_texto.regex' => 'El color del texto debe estar en formato HEX.',
        ];
    }
}
