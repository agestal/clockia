# Configurador de Negocio con IA

## Objetivo

Dar de alta un negocio nuevo a partir de la URL de su web, reduciendo al minimo la carga manual:

1. El admin introduce la URL.
2. Clockia inspecciona la web y prepara un borrador.
3. El admin revisa y corrige los datos.
4. Clockia crea el negocio y el usuario administrador.
5. El cliente entra al back y termina la configuracion operativa.

## Arquitectura propuesta

### Fase actual

Esta primera base deja preparado el flujo y ya resuelve el tramo operativo inicial:

- Sesiones de onboarding en BD.
- Descubrimiento guiado de la web con heuristicas y trazabilidad por URL.
- Borrador normalizado del negocio.
- Provisionado del negocio y del usuario administrador cuando el borrador ya tiene lo minimo.

### Fase siguiente

Sobre esta base se conectara el agente OpenAI con Responses API para mejorar la calidad de extraccion:

- Navegacion por herramientas en modo agentic.
- Extraccion estructurada con fuentes y confianza por campo.
- Background mode para procesos largos.
- Integracion futura con MCP remoto para tools propias.

## Modelo de datos

### business_onboarding_sessions

Representa un intento de alta de negocio.

- `source_url`: URL de entrada.
- `source_host`: host normalizado.
- `requested_tipo_negocio_id`: tipo de negocio elegido al arrancar.
- `requested_business_name`: nombre aportado por el admin si lo conoce.
- `requested_admin_name`, `requested_admin_email`, `requested_admin_password_hash`: datos del futuro admin.
- `draft_payload`: borrador normalizado.
- `missing_required_fields`: lista de campos minimos pendientes.
- `status`: `pending`, `discovering`, `needs_input`, `ready_for_review`, `provisioned`, `failed`.
- `provisioned_negocio_id`: negocio final si ya se creo.

### business_onboarding_sources

Guarda trazabilidad de cada pagina inspeccionada:

- URL
- rol detectado de la pagina
- titulo
- estado HTTP
- content type
- payload extraido

## Flujo backend

### 1. Crear sesion

Controlador admin:

- valida URL
- valida tipo de negocio
- acepta datos iniciales del admin
- crea la sesion

### 2. Discovery

Job de descubrimiento:

- visita home y un conjunto acotado de paginas internas
- prioriza contacto, horarios, experiencias y reservas
- extrae:
  - nombre
  - descripcion
  - email
  - telefono
  - direccion
  - horario
  - paginas candidatas de experiencias

### 3. Borrador

El resultado se guarda como JSON normalizado con esta forma:

```json
{
  "business": {
    "nombre": "Bodega Demo",
    "tipo_negocio_id": 1,
    "email": "info@bodega.test",
    "telefono": "+34 600 000 000",
    "zona_horaria": "Europe/Madrid",
    "descripcion_publica": "Texto publico",
    "direccion": "Direccion detectada",
    "url_publica": "https://bodega.test/"
  },
  "admin": {
    "name": "Admin Bodega Demo",
    "email": "admin@bodega.test",
    "password_ready": true
  },
  "experience_candidates": [],
  "opening_hours": [],
  "notes": [],
  "missing_required_fields": []
}
```

### 4. Provisionado

Accion transaccional:

- crea `Negocio`
- crea o reutiliza `User`
- vincula negocio y usuario
- genera plantillas de email por defecto
- genera encuesta por defecto

## Encaje con el agente OpenAI

Cuando conectemos OpenAI, la orquestacion ideal sera:

1. crear sesion de onboarding
2. lanzar un run del agente
3. usar tools controladas:
   - `fetch_url`
   - `render_page`
   - `extract_json_ld`
   - `read_pdf`
   - `list_internal_links`
4. producir el mismo `draft_payload`
5. dejar la misma pantalla de revision y el mismo paso de provisionado

La idea es que la IA cambie la calidad del descubrimiento, no el contrato interno del modulo.

## Criterios de seguridad

- solo URLs `http/https`
- bloqueo de hosts locales o IPs privadas evidentes
- crawl limitado por numero de paginas
- nada se provisiona automaticamente sin un paso explicito de confirmacion
- todas las paginas inspeccionadas quedan trazadas en BD

## Siguientes pasos recomendados

1. Hacer editable el borrador desde la propia ficha de onboarding.
2. Añadir provisionado de experiencias base a partir del borrador.
3. Integrar OpenAI Responses API.
4. Mover las tools de scraping a un servicio reutilizable o a MCP remoto.
