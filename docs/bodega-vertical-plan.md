# Vertical Bodega

## Objetivo

Reducir el producto a una vertical clara de bodegas y enoturismo:

- experiencias cerradas
- sesiones por día y hora
- reservas para grupos
- pago opcional o anticipado
- contacto del responsable

## Decisión de dominio

La unidad comercial principal deja de ser un "servicio genérico" y pasa a ser una **experiencia**.
La unidad operativa vendible deja de ser un "recurso con disponibilidad" y pasa a ser una **sesión**.

## Qué reutilizamos

- `negocios`
- `clientes`
- `servicios` como tabla física base de experiencias
- `reservas`
- `pagos`
- catálogos de `tipos_precio`, `estados_reserva`, `tipos_pago`, `estados_pago`
- configuración conversacional de `negocios`

## Qué sobra para la vertical bodega

- `servicio_recurso`
- `reserva_recursos`
- `recurso_combinaciones`
- lógica de combinación de recursos
- disponibilidad centrada en mesa/coche/habitación
- documentación obligatoria tipo rent a car
- integraciones y ocupaciones externas en la primera fase

## Modelo objetivo

### Negocio

Se mantiene. Útil para:

- identidad comercial
- datos públicos
- políticas
- configuración del chatbot
- automatizaciones de mail

### Servicio => Experiencia

Se mantiene la tabla `servicios`, pero conceptualmente pasa a ser `experiencias`.

Campos actuales que sí encajan:

- `nombre`
- `descripcion`
- `duracion_minutos`
- `precio_base`
- `tipo_precio_id`
- `requiere_pago`
- `activo`
- `notas_publicas`
- `instrucciones_previas`
- `horas_minimas_cancelacion`
- `es_reembolsable`
- `porcentaje_senal`

Campos a añadir:

- `numero_personas_minimo`
- `numero_personas_maximo`
- `permite_menores`
- `edad_minima`
- `precio_menor`
- `idiomas`
- `punto_encuentro`
- `incluye`
- `no_incluye`
- `accesibilidad_notas`
- `requiere_aprobacion_manual`

Campos a dejar de usar en bodega:

- `documentacion_requerida`
- `precio_por_unidad_tiempo`

### Nueva tabla: sesiones

Esta debe ser la tabla clave de la vertical.

Propuesta:

- `id`
- `negocio_id`
- `servicio_id`
- `recurso_id` nullable
- `fecha`
- `hora_inicio`
- `hora_fin`
- `aforo_total`
- `activo`
- `notas_publicas` nullable
- `estado` nullable si se quiere cerrar una sesión sin borrarla
- timestamps

La sesión representa una salida o pase concreto vendible.
Para la UI se puede seguir hablando de "disponibilidad", pero técnicamente debe existir una sesión real.

Ventajas:

- el calendario se vuelve explícito
- el aforo pertenece a la sesión
- la disponibilidad real es `aforo_total - plazas_reservadas`
- desaparece la lógica forzada de recursos + slots genéricos

### Recurso => Espacio opcional

`recursos` puede mantenerse solo como apoyo operativo opcional:

- sala principal
- sala privada
- terraza mirador

En la vertical bodega no debe ser obligatorio.
Si una experiencia no depende de una sala concreta, la sesión puede vivir sin `recurso_id`.

Campos que sí tienen sentido:

- `nombre`
- `activo`
- `notas_publicas`

Campos que dejan de ser prioritarios:

- `capacidad`
- `capacidad_minima`
- `combinable`

La capacidad comercial debe vivir en la sesión o en la experiencia, no en el recurso.

### Disponibilidades

La tabla actual `disponibilidades` deja de ser el eje principal.

Opciones:

1. Mantenerla solo como plantilla operativa interna para generar sesiones.
2. Eliminarla a medio plazo y trabajar solo con sesiones explícitas.

Recomendación:

- Fase 1: mantenerla solo si ayuda a generar sesiones.
- Fase 2: migrar la lógica al calendario de sesiones y dejar `disponibilidades` como legado eliminable.

### Reserva

`reservas` se mantiene, pero debe orientarse a sesión.

Propuesta:

- añadir `sesion_id` nullable al principio
- mantener de momento `servicio_id`
- hacer `recurso_id` nullable en la vertical bodega

Campos actuales válidos:

- `cliente_id`
- contacto snapshot del responsable
- `fecha`
- `hora_inicio`
- `hora_fin`
- `numero_personas`
- `precio_calculado`
- `precio_total`
- `estado_reserva_id`
- `notas`
- `localizador`

Campos útiles extra ya existentes:

- política de cancelación resuelta
- origen de reserva
- tracking de mails

Regla operativa nueva:

- la reserva consume plazas de una sesión
- no "ocupa un recurso" como en restaurante

### Pago

`pagos` se mantiene casi igual.

Encaja bien para bodegas:

- señal
- pago completo
- TPV online
- tarjeta

## CRUD y backoffice objetivo

### Módulos que sí deben quedar

- Negocios
- Experiencias
- Sesiones
- Reservas
- Clientes
- Pagos
- Calendario

### Módulos que deberían desaparecer del producto final de bodega

- Tipos de recurso
- Recursos como pieza principal
- Disponibilidades como CRUD protagonista
- Bloqueos genéricos
- ServicioRecurso
- RecursoCombinacion
- Ocupaciones externas

## Chat y tools objetivo

### Mantener

- `list_bookable_services`
- `get_service_details`
- `search_availability`
- `create_quote`
- `create_booking`
- `get_cancellation_policy`
- `get_arrival_instructions`

### Adaptar

- `search_availability` debe buscar sesiones con plazas libres, no recursos con huecos
- `create_booking` debe reservar plazas en una sesión
- `create_quote` debe calcular por persona, adultos/menores si aplica

### Eliminar complejidad sectorial sobrante

- documentación obligatoria de alquiler
- exposición de inventario técnico
- combinaciones de recursos
- ventanas de recogida/devolución

## Seeds objetivo

Dejar solo bodegas.

Propuesta inicial:

- una o varias bodegas de Rías Baixas
- experiencias reales
- sesiones reales por fecha/hora
- reservas demo coherentes
- pagos demo coherentes

Eliminar después:

- restaurante demo
- rent a car demo

## Orden recomendado

1. Añadir campos de experiencia específicos de bodega.
2. Crear tabla `sesiones`.
3. Adaptar `reservas` para poder apuntar a `sesion_id`.
4. Rehacer búsqueda de disponibilidad sobre sesiones.
5. Rehacer creación de reserva consumiendo aforo.
6. Rehacer seeds solo de bodega.
7. Simplificar calendario a sesiones y reservas.
8. Marcar recursos/disponibilidades como legado.
9. Eliminar legado cuando todo funcione en vertical bodega.

## Recomendación cerrada

No intentaría seguir empujando la lógica actual de `servicio + recurso + disponibilidad`.
Para bodega, el centro del sistema debe ser:

- experiencia
- sesión
- aforo
- reserva
- pago

Ese es el modelo que mejor encaja con visitas, catas y experiencias enoturísticas.
