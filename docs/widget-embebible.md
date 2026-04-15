# Widget embebible de reservas

Widget público de reservas para Clockia. Permite a cualquier negocio (bodegas, restaurantes, experiencias…) recibir reservas desde una web externa **usando exactamente la misma lógica de negocio que el chatbot**: mismos emails, mismas encuestas, mismas validaciones, mismo evento `BookingCreated`.

---

## 1. Arquitectura en un vistazo

```
┌───────────────────────┐        fetch        ┌────────────────────────────────┐
│ <clockia-widget>      │ ─────────────────►  │ /api/widget/businesses/{id}/…  │
│ Web Component / SD    │                     │ WidgetPublicController         │
└───────────────────────┘                     └──────────────┬─────────────────┘
                                                             │
             ┌────────────────────┬──────────────────────────┼───────────────────────┐
             ▼                    ▼                          ▼                       ▼
   ListBookableServicesTool  SearchAvailabilityTool    CreateQuoteTool   ReservationFinalizationService
             │                    │                          │                       │
             └────────────────────┴──────────────────────────┴───────────────────────┘
                                                │
                              El mismo núcleo que usa CreateBookingTool del chatbot
                              → BookingCreated event → emails → Google Calendar → encuestas
```

**El widget no duplica nada**. Cada endpoint público delega en las mismas clases existentes:

| Endpoint widget                             | Pieza reutilizada                                          |
|---------------------------------------------|------------------------------------------------------------|
| `GET /config`                               | Config pública del negocio (`Negocio::widgetSettingsResolved()`) |
| `GET /availability/calendar?year=&month=`   | Índice ligero sobre `Disponibilidad` + `Sesion` (con bloqueos) |
| `GET /availability/date?date=`              | `ListBookableServicesTool` + `SearchAvailabilityTool`      |
| `POST /availability/check`                  | `SearchAvailabilityTool` + `CreateQuoteTool`               |
| `POST /bookings`                            | **`ReservationFinalizationService::finalize()`** (idéntico al chatbot) |

La reserva final va por `app/Services/Reservations/ReservationFinalizationService.php`, que:
- Revalida el hueco con `SearchAvailabilityTool` en tiempo real.
- Crea/actualiza el `Cliente` por email/teléfono.
- Calcula el precio con `CreateQuoteTool`.
- Resuelve políticas con `PolicyResolver`.
- Crea la `Reserva` (que dispara `BookingCreated` → Google Calendar).
- Llama a `EnviarMailConfirmacion::dispatchSync()` si el negocio lo tiene activo.

No existe un "segundo flujo" de reservas: si arreglas el chatbot, arreglas el widget, y viceversa.

---

## 2. Ficheros añadidos

### Backend

- `database/migrations/2026_04_15_200001_add_widget_config_to_negocios_table.php`
  - Añade a `negocios`: `widget_enabled` (bool), `widget_public_key` (string unique), `widget_settings` (json).
  - Backfill: genera UUID para negocios existentes.
- `app/Http/Middleware/WidgetKeyAuth.php`
  - Resuelve el `Negocio` por `{business}`, valida que esté activo y con widget habilitado, y verifica la clave pública (`X-Widget-Key`, query `widget_key` o body). `hash_equals` para evitar timing attacks.
- `app/Http/Controllers/Widget/WidgetPublicController.php`
  - 5 métodos: `config`, `calendar`, `date`, `check`, `book`.
- `app/Models/Negocio.php`
  - Nuevos fillables/casts + `widgetSettingsResolved()` con defaults + `booted()` que autogenera la clave pública en `creating`.
- `app/Http/Requests/Admin/UpdateNegocioRequest.php`
  - Validación para `widget_enabled` y `widget_settings.*`.
- `routes/api.php`
  - Grupo `widget/businesses/{business}` con middlewares `throttle:60,1` + `widget.key`.
- `bootstrap/app.php`
  - Alias `widget.key` → `WidgetKeyAuth`.

### Frontend widget

- `vite.widget.config.js` — build en modo `lib/iife` a `public/widget/clockia-widget.js`.
- `package.json` — scripts `build:widget` y `dev:widget`.
- `resources/widget/index.js` — entry, registra `<clockia-widget>` y expone `window.Clockia.init()`.
- `resources/widget/clockia-widget.js` — clase `ClockiaWidget extends HTMLElement`, usa Shadow DOM.
- `resources/widget/api.js` — cliente HTTP tipado (fetch + `X-Widget-Key`).
- `resources/widget/styles.js` — CSS aislado inyectado en el Shadow DOM, con CSS vars alimentadas por la config.
- `resources/widget/utils.js` — helpers `h()`, formateo de fechas, precios.
- `resources/widget/components/calendar.js` — calendario mensual.
- `resources/widget/components/experience-list.js` — lista de experiencias + slots.
- `resources/widget/components/booking-form.js` — formulario final + resumen.

### Backoffice

- `resources/views/admin/negocios/_form.blade.php` — nueva sección "Widget embebible" (visible solo en edición) con toggle, campos visuales y snippet copiable.

---

## 3. Compilar y publicar

```bash
# instala deps si aún no las tienes
npm install

# build one-shot del widget embebible
npm run build:widget
# → public/widget/clockia-widget.js  (~25 kB, ~7 kB gzip)

# desarrollo con watch
npm run dev:widget
```

El fichero resultante (`public/widget/clockia-widget.js`) es un IIFE standalone sin dependencias externas. Puedes servirlo directamente desde Laravel (`/widget/clockia-widget.js`) o subirlo a un CDN.

---

## 4. Cómo se integra en una web externa

### Opción A — Custom Element (recomendado)

```html
<script src="https://TU_DOMINIO/widget/clockia-widget.js" defer></script>

<clockia-widget
    business-id="8"
    widget-key="cea60fa5-fcf4-40f7-baff-e57333a802d2"
    api-base="https://TU_DOMINIO/api/widget"
    primary-color="#7B3F00"
    secondary-color="#EAD7C5"
    text-color="#2B2B2B"
    background-color="#FFFFFF"
    font-family="Inter, sans-serif"
    font-size-base="14px"
    border-radius="10px"
    locale="es"
></clockia-widget>
```

Los atributos `primary-color`, `secondary-color`, etc. son **opcionales**: si no los pasas, se usan los guardados en el backoffice. Si los pasas, **sobrescriben** lo guardado.

### Opción B — Inicialización por JavaScript

```html
<div id="clockia-widget"></div>
<script src="https://TU_DOMINIO/widget/clockia-widget.js"></script>
<script>
  Clockia.init({
    businessId: 8,
    widgetKey: 'cea60fa5-fcf4-40f7-baff-e57333a802d2',
    apiBase: 'https://TU_DOMINIO/api/widget',
    container: '#clockia-widget',
    primaryColor: '#7B3F00',
    secondaryColor: '#EAD7C5',
    textColor: '#2B2B2B',
    backgroundColor: '#FFFFFF',
    fontFamily: 'Inter, sans-serif',
    fontSizeBase: '14px',
    borderRadius: '10px',
    locale: 'es',
  });
</script>
```

Ambas opciones funcionan equivalentemente. La opción A monta el componente declarativamente; la B es un wrapper que crea el elemento e inyecta atributos.

---

## 5. Endpoints REST expuestos

Base: `/api/widget/businesses/{business}` (protegido por `widget.key` middleware + `throttle:60,1`).

Todas las llamadas requieren la clave:
- Header: `X-Widget-Key: <uuid>`
- O query: `?widget_key=<uuid>`
- O body: `{ "widget_key": "<uuid>" }`

### `GET /config`

```json
{
  "business": { "id": 8, "name": "Bodega X", "timezone": "Europe/Madrid", "description": null },
  "widget": {
    "locale": "es", "timezone": "Europe/Madrid", "currency": "EUR",
    "primary_color": "#7B3F00", "secondary_color": "#EAD7C5",
    "text_color": "#2B2B2B", "background_color": "#FFFFFF",
    "font_family": "Inter, system-ui, sans-serif",
    "font_size_base": "14px", "border_radius": "10px"
  }
}
```

### `GET /availability/calendar?year=2026&month=4`

```json
{
  "year": 2026, "month": 4,
  "days": [
    { "date": "2026-04-01", "available": false, "is_past": true },
    { "date": "2026-04-16", "available": true,  "is_past": false },
    ...
  ]
}
```

Optimizado: **no** recorre todos los slots; consulta un índice de `Disponibilidad` por `dia_semana` y añade fechas con `Sesion` activa.

### `GET /availability/date?date=2026-04-16&participants=2`

```json
{
  "date": "2026-04-16",
  "services": [
    {
      "id": 10,
      "name": "Visita con cata",
      "description": "Recorrido guiado y degustación",
      "duration_minutes": 90,
      "price": "25.00",
      "currency": "EUR",
      "min_participants": 2,
      "max_participants": 12,
      "requires_timeslot": true,
      "timeslots": [
        { "time": "11:00", "end_time": "12:30", "slot_key": "abc…", "available": true, "session_id": null, "seats_remaining": null },
        { "time": "13:00", "end_time": "14:30", "slot_key": "def…", "available": true, "session_id": null, "seats_remaining": null }
      ],
      "availability_mode": "precise",
      "requires_documentation": false,
      "public_notes": null,
      "includes": null,
      "languages": null
    }
  ]
}
```

Internamente itera los servicios del negocio, para cada uno llama `SearchAvailabilityTool::execute()` y filtra los que no tengan huecos salvo que estén en modo `simple` (servicios sin agenda detallada, que el negocio confirma manualmente).

### `POST /availability/check`

Request:
```json
{
  "service_id": 10,
  "date": "2026-04-16",
  "time": "11:00",
  "participants": 4
}
```

Response (200):
```json
{
  "available": true,
  "currency": "EUR",
  "summary": { "unit_price": 25.0, "participants": 4, "total_price": 100.00 },
  "slot": { "slot_key": "abc…", "start_time": "11:00", "end_time": "12:30" }
}
```

Valida min/max participantes del servicio y que el hueco siga existiendo. Calcula precio con `CreateQuoteTool` (la misma que el chatbot).

### `POST /bookings`

Request:
```json
{
  "service_id": 10,
  "date": "2026-04-16",
  "time": "11:00",
  "slot_key": "abc…",
  "participants": 4,
  "customer": {
    "name": "Juan",
    "last_name": "Pérez",
    "email": "juan@example.com",
    "phone": "+34600111222"
  },
  "notes": "Iremos con un niño"
}
```

Response (201):
```json
{
  "success": true,
  "booking": {
    "id": 999,
    "reference": "CLK-2026-000999",
    "status": "Confirmada",
    "service_name": "Visita con cata",
    "date": "2026-04-16",
    "time": "11:00",
    "end_time": "12:30",
    "participants": 4,
    "total_price": "100.00",
    "currency": "EUR"
  },
  "messages": ["Reserva creada correctamente."]
}
```

Errores de validación o hueco perdido devuelven 422 con `{ "success": false, "error": "..." }`. El widget los muestra al usuario sin perder el resto del flujo.

**Este endpoint delega íntegramente en `ReservationFinalizationService::finalize(CreateBookingInput)`**. Es exactamente la misma clase y método que usa `CreateBookingTool` del chatbot (ver `app/Tools/Reservations/CreateBookingTool.php:104`). No hay lógica paralela.

---

## 6. Seguridad

- **Clave pública por negocio** (UUID v4, guardada en `negocios.widget_public_key`). No es un secreto, pero actúa como identificador inmune a fuerza bruta y permite revocarla generando una nueva.
- **Middleware `widget.key`**: valida con `hash_equals` (constant-time), rechaza si `widget_enabled=false` o si el negocio no está activo.
- **Rate limiting**: `throttle:60,1` → 60 req/min por IP sobre todo el grupo `widget/*`.
- **CORS**: permitido en `api/*` con `allowed_origins: ['*']` (ajustable en `config/cors.php` para producción si quieres restringir dominios).
- **No expone datos privados**: `GET /config` solo devuelve nombre, timezone, descripción pública y settings visuales. `GET /availability/date` solo devuelve campos "clientes" del servicio, nunca IDs internos de recursos ni configuración interna.
- **Validación servidor**: plazas min/max, fecha en formato ISO, revalidación de hueco en `finalize()`. El widget puede mentir, pero el backend rechaza.
- **Mejora futura (no implementada, fácil de añadir)**: campo `widget_allowed_domains` (JSON) en `negocios` y check de `Origin`/`Referer` en el middleware.

---

## 7. Configurar un negocio para usar el widget

1. Ir al backoffice → `admin/negocios/{id}/edit`.
2. Bajar hasta la sección **"Widget embebible"**.
3. Activar el toggle "Activar widget público".
4. Opcionalmente personalizar colores, tipografía, border radius, idioma.
5. Guardar.
6. Copiar el snippet del final de la sección y pegarlo en la web del cliente.

La clave pública se autogenera en `creating` del modelo `Negocio`, así que cualquier negocio nuevo ya viene con su `widget_public_key` listo (aunque con `widget_enabled=false`).

Para regenerar la clave pública (revocar el widget actual y emitir uno nuevo), ejecutar:

```bash
php artisan tinker --execute="
\$n = App\\Models\\Negocio::find(8);
\$n->widget_public_key = (string) \\Illuminate\\Support\\Str::uuid();
\$n->save();
echo \$n->widget_public_key;
"
```

---

## 8. Flujos cubiertos por el widget

- **Servicio con horarios**: calendario → día disponible → experiencia → slot horario → formulario → reserva.
- **Servicio sin horarios** (`requires_timeslot=false`): calendario → día disponible → experiencia → formulario directo → reserva por día.
- **Validación participantes**: si el usuario introduce menos del mínimo o más del máximo, el frontend bloquea y, si se escapa, el endpoint `/check` o `/bookings` lo rechaza con 422.
- **Disponibilidad perdida (race condition)**: entre que el usuario elige y envía, otro cliente reserva. `ReservationFinalizationService::resolveSlot()` revalida y lanza excepción; el widget la muestra y permite volver al paso anterior.
- **Carga y errores de red**: loader spinner, mensajes de error claros, botón "Reintentar".

---

## 9. Cómo extenderlo

- **Nuevo negocio / sector**: no hace falta tocar el widget. Si el sector nuevo tiene servicios con su propia `Disponibilidad`/`Sesion`, el endpoint `/availability/date` los encontrará automáticamente.
- **Nuevos campos en el formulario**: añadir input en `resources/widget/components/booking-form.js` y pasarlo en `submitBooking()` hacia `customer.*` o `notes`. El backend acepta los mismos campos que `CreateBookingInput`, así que también puede enviar `document_type`/`document_value` si el servicio los requiere.
- **Cambios visuales globales**: editar `resources/widget/styles.js` (CSS vars alimentadas por la config) y `npm run build:widget`.
- **Restricción de dominios**: añadir campo `widget_allowed_domains` (JSON array) en `negocios` y comprobar `$request->header('Origin')` en `WidgetKeyAuth`.

---

## 10. Checklist de entrega

- [x] Migración + campos en `negocios`
- [x] Middleware `WidgetKeyAuth` con validación en tiempo constante
- [x] `WidgetPublicController` con 5 endpoints
- [x] Reutilización real de `ReservationFinalizationService` (idéntico al chatbot)
- [x] Rutas `api/widget/*` con throttling y CORS
- [x] Web Component `<clockia-widget>` con Shadow DOM
- [x] `Clockia.init()` como wrapper JS
- [x] Personalización visual por atributo HTML o por JS
- [x] Vite build standalone `npm run build:widget`
- [x] Backoffice: toggle, campos visuales, snippet copiable
- [x] Documentación con ejemplos de integración
- [x] Smoke test: config, calendar, date, clave inválida → 401, widget.js servido
