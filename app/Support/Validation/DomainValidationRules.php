<?php

namespace App\Support\Validation;

use Illuminate\Validation\Rule;

final class DomainValidationRules
{
    public static function tipoNegocioStoreRules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
        ];
    }

    public static function negocioStoreRules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
            'tipo_negocio_id' => ['required', 'integer', 'exists:tipos_negocio,id'],
            'email' => ['nullable', 'email', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:255'],
            'zona_horaria' => ['sometimes', 'string', 'timezone'],
            'activo' => ['sometimes', 'boolean'],
        ];
    }

    public static function clienteStoreRules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:255'],
            'notas' => ['nullable', 'string'],
        ];
    }

    public static function tipoPrecioStoreRules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
        ];
    }

    public static function servicioStoreRules(): array
    {
        return [
            'negocio_id' => ['required', 'integer', 'exists:negocios,id'],
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'duracion_minutos' => ['required', 'integer', 'min:1'],
            'precio_base' => ['required', 'numeric', 'min:0'],
            'tipo_precio_id' => ['required', 'integer', 'exists:tipos_precio,id'],
            'requiere_pago' => ['sometimes', 'boolean'],
            'activo' => ['sometimes', 'boolean'],
        ];
    }

    public static function tipoRecursoStoreRules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
        ];
    }

    public static function recursoStoreRules(): array
    {
        return [
            'negocio_id' => ['required', 'integer', 'exists:negocios,id'],
            'nombre' => ['required', 'string', 'max:255'],
            'tipo_recurso_id' => ['required', 'integer', 'exists:tipos_recurso,id'],
            'capacidad' => ['nullable', 'integer', 'min:1'],
            'activo' => ['sometimes', 'boolean'],
        ];
    }

    public static function servicioRecursoStoreRules(): array
    {
        return [
            'servicio_id' => ['required', 'integer', 'exists:servicios,id'],
            'recurso_id' => [
                'required',
                'integer',
                'exists:recursos,id',
                Rule::unique('servicio_recurso', 'recurso_id')->where(
                    static fn ($query) => $query->where('servicio_id', request('servicio_id'))
                ),
            ],
        ];
    }

    public static function disponibilidadStoreRules(): array
    {
        return [
            'recurso_id' => ['required', 'integer', 'exists:recursos,id'],
            'dia_semana' => ['required', 'integer', Rule::in([0, 1, 2, 3, 4, 5, 6])],
            'hora_inicio' => ['required', 'date_format:H:i'],
            'hora_fin' => ['required', 'date_format:H:i', 'after:hora_inicio'],
            'activo' => ['sometimes', 'boolean'],
        ];
    }

    public static function tipoBloqueoStoreRules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
        ];
    }

    public static function bloqueoStoreRules(): array
    {
        return [
            'recurso_id' => ['required', 'integer', 'exists:recursos,id'],
            'tipo_bloqueo_id' => ['required', 'integer', 'exists:tipos_bloqueo,id'],
            'fecha' => ['required', 'date'],
            'hora_inicio' => ['nullable', 'date_format:H:i'],
            'hora_fin' => ['nullable', 'date_format:H:i'],
            'motivo' => ['nullable', 'string', 'max:255'],
        ];
    }

    public static function estadoReservaStoreRules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
        ];
    }

    public static function reservaStoreRules(): array
    {
        return [
            'negocio_id' => ['required', 'integer', 'exists:negocios,id'],
            'servicio_id' => ['required', 'integer', 'exists:servicios,id'],
            'recurso_id' => ['required', 'integer', 'exists:recursos,id'],
            'cliente_id' => ['required', 'integer', 'exists:clientes,id'],
            'fecha' => ['required', 'date'],
            'hora_inicio' => ['required', 'date_format:H:i'],
            'hora_fin' => ['required', 'date_format:H:i', 'after:hora_inicio'],
            'numero_personas' => ['nullable', 'integer', 'min:1'],
            'precio_calculado' => ['required', 'numeric', 'min:0'],
            'precio_total' => ['nullable', 'numeric', 'min:0'],
            'estado_reserva_id' => ['required', 'integer', 'exists:estados_reserva,id'],
            'notas' => ['nullable', 'string'],
        ];
    }

    public static function tipoPagoStoreRules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
        ];
    }

    public static function estadoPagoStoreRules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
        ];
    }

    public static function pagoStoreRules(): array
    {
        return [
            'reserva_id' => ['required', 'integer', 'exists:reservas,id'],
            'tipo_pago_id' => ['required', 'integer', 'exists:tipos_pago,id'],
            'estado_pago_id' => ['required', 'integer', 'exists:estados_pago,id'],
            'importe' => ['required', 'numeric', 'min:0'],
            'referencia_externa' => ['nullable', 'string', 'max:255'],
            'fecha_pago' => ['nullable', 'date'],
        ];
    }
}
