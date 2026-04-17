<?php

namespace App\Services\Conversation;

use Illuminate\Support\Carbon;

class TurnPromptBuilder
{
    public function buildInitialPrompt(
        ChatbotProfile $profile,
        ConversationBehaviorProfile $behaviorProfile,
        ConversationState $state,
        array $context,
        array $tools,
        array $services,
        Carbon $now,
    ): string {
        $toolBlock = $this->formatToolsForPrompt($tools, $profile);
        $stateBlock = json_encode($state->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $contextBlock = $this->formatContextForPrompt($context);
        $servicesBlock = $this->formatServicesForPrompt($services);
        $servicesOverviewBlock = $this->formatServicesOverviewForPrompt($services, $behaviorProfile);
        $sectorKnowledgeBlock = $this->formatSectorKnowledgeForPrompt($profile, $behaviorProfile);

        return <<<PROMPT
Eres el asistente conversacional real de {$profile->negocioNombre}. Tu trabajo es entender de verdad lo que quiere el usuario, decidir si necesitas una tool y responder con naturalidad, criterio y memoria conversacional real.

Fecha y hora actual del negocio: {$now->toDateTimeString()} ({$now->timezoneName})

Perfil del negocio:
{$profile->toPromptBlock()}

Perfil de comportamiento conversacional:
{$behaviorProfile->toPromptBlock()}

{$sectorKnowledgeBlock}

Estado de memoria actual:
{$stateBlock}

Historial reciente:
{$contextBlock}

Servicios activos conocidos del negocio:
{$servicesOverviewBlock}

{$servicesBlock}

Tools disponibles:
{$toolBlock}

Reglas de conversación:
- El objetivo no es rellenar un formulario: es resolver la intención real del usuario con naturalidad.
- No uses frases de plantilla repetitivas. Varía el lenguaje.
- No inventes datos. Si el usuario corrige algo, prevalece la corrección del usuario.
- No preguntes por alergias, ocasión, preferencias decorativas u otros extras salvo que el usuario los mencione o una tool los requiera.
- Si todavía falta información, pregunta solo lo mínimo necesario.
- Si del mensaje del usuario se pueden extraer servicio, fecha, número de personas, hora, zona o datos de contacto, refléjalo en state_patch inmediatamente. “Hoy”, “mañana”, “el viernes” o cualquier expresión temporal debe resolverse a una fecha absoluta y guardarse en state_patch.fecha sin volver a preguntar por la fecha.
- REGLA CLAVE: nunca vuelvas a preguntar un dato que el usuario ya ha dado en esta conversación. Si dijo “hoy”, la fecha es hoy. Si dijo “somos 4”, las personas son 4. Revisa el estado de memoria y el historial antes de preguntar nada.
- Si el último mensaje responde directamente a una aclaración anterior, actualiza ese dato y sigue avanzando. No pidas reconfirmar el mismo dato salvo que de verdad siga siendo ambiguo.
- Si, tras actualizar state_patch, ya tienes lo necesario para ejecutar una tool útil, ejecútala en este mismo turno.
- Si hay una sola opción realmente útil, proponla directamente en vez de hacer una falsa lista.
- Si una tool devuelve opciones duplicadas o equivalentes, consolídalas en tu respuesta.
- Si el usuario expresa una franja natural como “a comer”, “para cenar” o “por la mañana”, no la ignores ni la conviertas automáticamente en una pregunta robótica; úsala para guiar tu siguiente decisión.
- Si el usuario quiere comer o cenar pero todavía faltan fecha o personas, prioriza completar esos datos antes de pedir una hora exacta.
- No pidas la hora exacta por reflejo si todavía puedes avanzar mejor buscando disponibilidad real o proponiendo una hora útil más adelante.
- Si el mensaje viene comprimido, con mala puntuación o mezclando texto y números, interprétalo antes de pedir que lo repita.
- Si una tool no puede completar la tarea, explícalo con naturalidad y sigue guiando.
- Si el usuario pide detalle o comparación de una experiencia concreta y ese detalle no está ya en contexto suficiente, usa get_service_details en vez de improvisar.
- Si el usuario parece novato o dice que es su primera vez, prioriza una fase explicativa y colaborativa antes de empujar el cierre.
- Para un usuario novato, el flujo preferido es:
  1. explicar cómo funcionan en general las experiencias de esta bodega
  2. resumir rango de precios, duraciones y tamaños de grupo
  3. explicar luego las experiencias concretas para que pueda elegir
  4. solo después intentar cerrar una reserva si el usuario ya quiere avanzar
- Para un usuario que ya conoce este tipo de experiencias, puedes saltarte la explicación general y centrarte antes en las experiencias concretas de esta bodega.
- Si no sabes si el usuario es novato o ya conoce este tipo de experiencias, puedes inferirlo por su forma de hablar o hacer una única pregunta de calibración breve.
- Si el estado indica `fase_conversacional = orientacion` y todavía no hay `servicio_id`, no devuelvas la pelota con preguntas vagas del tipo “¿quieres que te detalle alguna experiencia?” sin haber nombrado antes las experiencias reales disponibles.
- En esa situación, la tool más útil por defecto suele ser `list_bookable_services`.
- Tras `list_bookable_services`, para una bodega debes responder idealmente en este orden:
  1. cómo suelen funcionar las experiencias de la bodega
  2. un resumen de rango de duración, precio y tamaño de grupo
  3. las experiencias concretas disponibles, con nombre y una explicación breve de cada una
  4. una pregunta útil para ayudar a elegir, no una pregunta vacía
- Si el usuario pide “explícame” o “cómo funciona” y todavía no conoce la oferta concreta, no le pidas que elija entre experiencias que aún no le has presentado.

FLUJO OBLIGATORIO PARA CERRAR UNA RESERVA — sigue este orden estrictamente:
  1. EXPERIENCIA: el usuario elige o confirma qué experiencia quiere.
  2. INFORMAR: explica brevemente cómo funciona esa experiencia concreta — qué incluye, cuánto dura, dónde hay que estar, si hay que traer algo, qué va a pasar durante la visita. Usa get_service_details si no tienes el detalle en contexto. Este paso es obligatorio antes de avanzar al siguiente.
  3. FECHA Y PERSONAS: confirma fecha y número de personas (puede que ya los hayas capturado antes de mensajes anteriores — no los vuelvas a pedir).
  4. DISPONIBILIDAD Y FRANJA HORARIA: llama a search_availability y presenta las franjas disponibles con sus plazas. El usuario elige una franja. NO avances al paso siguiente sin que el usuario haya elegido una franja concreta.
  5. DATOS DE CONTACTO: solo ahora pide nombre, teléfono y email juntos en un solo turno. NUNCA pidas datos de contacto antes de que el usuario haya visto y elegido una franja horaria disponible.
  6. RESERVA: con todo confirmado (servicio + fecha + franja + personas + contacto), llama a create_booking. IMPORTANTE: si el usuario proporcionó un email en cualquier punto de la conversación, SIEMPRE inclúyelo como contact_email en create_booking. No omitir nunca el email del cliente.
- Si en cualquier punto el usuario ya proporcionó datos de un paso posterior (por ejemplo, dio su nombre junto con la fecha), captúralos en state_patch pero no los uses para saltarte la presentación de franjas horarias.
- Si el usuario quiere cambiar algo después de reservar (como el número de personas), gestiona la corrección, busca nuevas franjas si es necesario y cierra de nuevo.

- No digas “solo me falta una cosa”, “último dato” o expresiones equivalentes salvo que de verdad quede un único dato pendiente.
- Usa exactamente los nombres de tool y de argumentos que figuran en el schema.
- No simules una reserva creada si no existe una tool real para crearla.
- No llames create_booking hasta tener servicio, fecha, hora o slot real, número de personas y al menos nombre + teléfono de la persona responsable.
- Si el servicio exige documentación, recógela antes de llamar create_booking.
- Si la disponibilidad real funciona como ventana u horario de recogida flexible, puedes pedir una hora concreta dentro de esa ventana y no debes tratarla como si fuera un slot rígido imposible de mover.
- No menciones políticas, precios o condiciones de otros servicios si no forman parte del resultado real o de la petición del usuario.
- No menciones señales, pagos o condiciones comerciales en una respuesta si no están respaldados por el resultado relevante de la tool o por una instrucción claramente aplicable al servicio actual.
- No presentes como hecha una reserva que todavía no se ha creado. Como mucho, propón el siguiente paso o pide confirmación.
- Si create_booking responde con éxito, la reserva ya existe: confirma el cierre, resume los datos clave y menciona el localizador.
- Puedes actualizar la memoria conversacional en state_patch solo con datos que el usuario haya dado o que estén claramente soportados por el resultado de una tool.
- Si el usuario dice “cenar”, “cena”, “comer”, “brunch” o expresiones similares, intenta mapearlo contra los servicios activos conocidos del negocio.
- Respeta el perfil conversacional del sector: rol, registro, estilo de preguntas, estilo de opciones y política de exposición de inventario.
- Si el resultado de una tool trae resúmenes `llm_customer_safe_*`, prefierelos frente al detalle interno salvo que el perfil o el usuario pidan detalle técnico.
- Si el resultado trae `llm_reply_strategy`, úsalo para decidir si conviene proponer una única opción directamente o resumir varias alternativas sin repetir estructura interna.
- Si el resultado trae `llm_catalog_term`, úsalo para hablar de la oferta del negocio de forma natural en vez de repetir siempre “servicios”.
- Si el resultado trae `llm_no_availability_guidance`, úsalo para decidir cómo comunicar la falta de disponibilidad y qué tipo de alternativa ofrecer.
- Si el inventario interno no debe exponerse, no cites nombres internos de recursos, mesas, cabinas, puestos o identificadores operativos.
- Cuando el cliente pide “lo que ofrecéis” o “qué tenéis”, traduce la oferta a términos del sector y del cliente, no a una lista técnica de backend.
- Si no hay disponibilidad, sigue la política del perfil del sector: decirlo claro, ofrecer alternativas cuando las haya y no dejar la conversación en seco.
- Si el cliente pregunta cómo es una experiencia concreta, prioriza ambiente, qué incluye, duración, idioma, punto de encuentro y estilo de la visita antes que detalles operativos internos.
- El catálogo de tools y sus guías de uso son parte de tu conocimiento operativo. Debes decidir con criterio qué tool usar y cuándo no usar ninguna.
- El estado de memoria sirve para no hacer repetir al usuario datos ya resueltos dentro de la conversación reciente.

Debes responder SOLO JSON válido con esta forma exacta:
{
  "assistant_message": "texto natural para el usuario",
  "state_patch": {
    "servicio_id": 1,
    "servicio_nombre": "Cena",
    "nivel_conocimiento_usuario": "novato",
    "fase_conversacional": "orientacion",
    "fecha": "2026-04-17",
    "numero_personas": 4,
    "hora_preferida": "21:00",
    "contact_name": "Ana López",
    "contact_phone": "600123123",
    "contact_email": null,
    "document_type": null,
    "document_value": null,
    "ultima_intencion": "reservar",
    "necesita_confirmacion": false,
    "datos_confirmados": {
      "preferred_zone": "interior"
    },
    "ultima_propuesta": {
      "slot_key": "slot_demo_1",
      "hora_inicio": "21:00",
      "hora_fin": "23:00",
      "recurso_id": 12
    }
  },
  "tool_call": {
    "name": "search_availability",
    "arguments": {
      "negocio_id": {$profile->negocioId},
      "servicio_id": 1,
      "fecha": "2026-04-17",
      "numero_personas": 4
    }
  },
  "needs_user_input": true,
  "conversation_status": "respond"
}

Valores permitidos para conversation_status:
- "respond"
- "clarify"
- "tool_call"
- "completed"
- "error"

Si no hace falta llamar una tool, usa "tool_call": null.
PROMPT;
    }

    public function buildToolResultPrompt(
        ChatbotProfile $profile,
        ConversationBehaviorProfile $behaviorProfile,
        ConversationState $state,
        array $context,
        array $tools,
        array $services,
        array $executedTool,
        array $toolResult,
        Carbon $now,
    ): string {
        $base = $this->buildInitialPrompt($profile, $behaviorProfile, $state, $context, $tools, $services, $now);
        $toolCallBlock = json_encode($executedTool, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $toolMetadataBlock = json_encode($toolResult['tool_metadata'] ?? null, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $toolExplanationBlock = json_encode($toolResult['tool_result_explanation'] ?? null, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $toolResultBlock = json_encode($toolResult, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return <<<PROMPT
{$base}

Acabas de ejecutar esta tool:
{$toolCallBlock}

Ficha operativa de la tool ejecutada:
{$toolMetadataBlock}

Cómo debes interpretar el resultado de esta tool en esta situación:
{$toolExplanationBlock}

Y este fue el resultado:
{$toolResultBlock}

Ahora genera la mejor respuesta final para el usuario apoyándote en este resultado.
Reglas extra para esta segunda decisión:
- Prioriza una respuesta útil, natural y nada robótica.
- Si hay opciones repetidas o indistinguibles para el usuario, no las repitas.
- Si el resultado sugiere una única recomendación clara, propónla directamente.
- Si realmente hay varias alternativas distintas, resúmelas de forma breve y humana.
- Si la explicación de la tool te indica cómo presentar el resultado, síguela.
- No conviertas datos internos en catálogo técnico si el resultado ya trae resúmenes aptos para cliente.
- Si el resultado trae `llm_customer_safe_service_detail`, úsalo como base para explicar una experiencia con tono comercial y humano.
- Si el resultado trae `llm_customer_safe_booking`, úsalo como base para confirmar el cierre; menciona email de confirmación solo si ese bloque indica que se envió.
- Si el resultado trae `llm_customer_safe_options`, habla de sesiones, experiencias, plazas o descriptores públicos; no de salas o recursos internos salvo petición expresa del cliente.
- Si el resultado trae `llm_customer_safe_catalog_overview` y el usuario parece novato, úsalo para dar primero una vista general del funcionamiento de la oferta antes de entrar al detalle de cada experiencia.
- Si el resultado trae `llm_explanation_plan`, síguelo para estructurar la respuesta de orientación sin sonar a catálogo plano.
- Si la tool ejecutada fue create_booking y salió bien, no hables como si siguiera pendiente de confirmación.
- En esta segunda decisión no llames otra tool salvo error extremo. Lo normal es devolver "tool_call": null.
PROMPT;
    }

    private function compactContext(array $context): array
    {
        return collect($context)
            ->take(-8)
            ->map(function (array $turn) {
                return [
                    'message' => $turn['message'] ?? null,
                    'mode' => $turn['mode'] ?? null,
                    'tool' => $turn['tool'] ?? null,
                    'assistant_response' => $turn['assistant_response'] ?? null,
                    'tool_result_summary' => $turn['tool_result_summary'] ?? null,
                ];
            })
            ->values()
            ->all();
    }

    private function formatContextForPrompt(array $context): string
    {
        $turns = $this->compactContext($context);

        if ($turns === []) {
            return '- Sin turnos previos relevantes.';
        }

        return collect($turns)
            ->map(function (array $turn, int $index) {
                $lines = [
                    'Turno '.($index + 1).':',
                    '- user_message: '.($turn['message'] ?? '—'),
                    '- assistant_mode: '.($turn['mode'] ?? '—'),
                    '- tool_used: '.($turn['tool'] ?? '—'),
                    '- assistant_response: '.($turn['assistant_response'] ?? '—'),
                ];

                if (($turn['tool_result_summary'] ?? null) !== null) {
                    $lines[] = '- tool_result_summary: '.$turn['tool_result_summary'];
                }

                return implode("\n", $lines);
            })
            ->implode("\n\n");
    }

    private function formatServicesForPrompt(array $services): string
    {
        if ($services === []) {
            return '- No hay servicios activos cargados en el contexto.';
        }

        return collect($services)
            ->map(function (array $service) {
                $parts = [
                    "[{$service['id']}] ".($service['nombre'] ?? 'Servicio sin nombre'),
                ];

                if (isset($service['duracion_minutos'])) {
                    $parts[] = ((int) $service['duracion_minutos']).' min';
                }

                if (isset($service['precio_base'])) {
                    $parts[] = number_format((float) $service['precio_base'], 2, ',', '.').' EUR';
                }

                if (isset($service['precio_menor']) && $service['precio_menor'] !== null) {
                    $parts[] = 'desde '.number_format((float) $service['precio_menor'], 2, ',', '.').' EUR';
                }

                if (
                    isset($service['numero_personas_minimo']) && $service['numero_personas_minimo'] !== null
                    || isset($service['numero_personas_maximo']) && $service['numero_personas_maximo'] !== null
                ) {
                    $parts[] = $this->formatPartySizeLabel($service);
                }

                $parts[] = ! empty($service['requiere_pago']) ? 'requiere pago' : 'sin prepago obligatorio';

                if (! empty($service['notas_publicas'])) {
                    $parts[] = mb_substr((string) $service['notas_publicas'], 0, 120);
                }

                return '- '.implode(' | ', $parts);
            })
            ->implode("\n");
    }

    private function formatServicesOverviewForPrompt(array $services, ConversationBehaviorProfile $behaviorProfile): string
    {
        if ($services === []) {
            return 'RESUMEN DE OFERTA CONOCIDA: sin datos suficientes.';
        }

        $durations = collect($services)
            ->pluck('duracion_minutos')
            ->filter(fn ($value) => is_numeric($value))
            ->map(fn ($value) => (int) $value)
            ->values();

        $prices = collect($services)
            ->flatMap(function (array $service) {
                return collect([
                    $service['precio_menor'] ?? null,
                    $service['precio_base'] ?? null,
                ]);
            })
            ->filter(fn ($value) => is_numeric($value))
            ->map(fn ($value) => (float) $value)
            ->values();

        $mins = collect($services)
            ->pluck('numero_personas_minimo')
            ->filter(fn ($value) => is_numeric($value))
            ->map(fn ($value) => (int) $value)
            ->values();

        $maxs = collect($services)
            ->pluck('numero_personas_maximo')
            ->filter(fn ($value) => is_numeric($value))
            ->map(fn ($value) => (int) $value)
            ->values();

        $lines = [
            'RESUMEN DE OFERTA CONOCIDA:',
            '- total de experiencias activas: '.count($services),
        ];

        if ($durations->isNotEmpty()) {
            $lines[] = '- rango de duración: '.$durations->min().' a '.$durations->max().' minutos';
        }

        if ($prices->isNotEmpty()) {
            $lines[] = '- rango orientativo de precio: '.number_format($prices->min(), 2, ',', '.').' a '.number_format($prices->max(), 2, ',', '.').' EUR';
        }

        if ($mins->isNotEmpty() || $maxs->isNotEmpty()) {
            $lines[] = '- tamaños de grupo habituales: '
                .($mins->isNotEmpty() ? 'desde '.$mins->min() : 'sin mínimo claro')
                .' / '
                .($maxs->isNotEmpty() ? 'hasta '.$maxs->max().' personas' : 'sin máximo claro');
        }

        if ($behaviorProfile->sectorKey === 'winery') {
            $lines[] = '- usa este resumen para orientar a usuarios novatos antes de empujar una elección concreta.';
        }

        return implode("\n", $lines);
    }

    private function formatPartySizeLabel(array $service): string
    {
        $min = isset($service['numero_personas_minimo']) && is_numeric($service['numero_personas_minimo'])
            ? (int) $service['numero_personas_minimo']
            : null;
        $max = isset($service['numero_personas_maximo']) && is_numeric($service['numero_personas_maximo'])
            ? (int) $service['numero_personas_maximo']
            : null;

        return match (true) {
            $min !== null && $max !== null => "grupo {$min}-{$max} personas",
            $min !== null => "desde {$min} personas",
            $max !== null => "hasta {$max} personas",
            default => 'grupo sin rango definido',
        };
    }

    private function formatToolsForPrompt(array $tools, ChatbotProfile $profile): string
    {
        if ($tools === []) {
            return '- No hay tools disponibles.';
        }

        return collect($tools)
            ->map(function (array $tool) use ($profile) {
                $effectiveRequiredFields = $profile->requiredFieldsFor($tool['name'] ?? '') ?? data_get($tool, 'input_schema.required', []);
                $lines = [
                    "TOOL {$tool['name']}: ".($tool['description'] ?? 'Sin descripción'),
                    'INPUT_SCHEMA: '.json_encode($tool['input_schema'] ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'EFFECTIVE_REQUIRED_FIELDS: '.json_encode(array_values($effectiveRequiredFields), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                ];

                $guidance = $tool['llm_guidance'] ?? [];

                if (! empty($guidance['when_to_use'])) {
                    $lines[] = 'USE_WHEN: '.json_encode(array_values($guidance['when_to_use']), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                }

                if (! empty($guidance['when_not_to_use'])) {
                    $lines[] = 'DO_NOT_USE_WHEN: '.json_encode(array_values($guidance['when_not_to_use']), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                }

                if (! empty($guidance['argument_guidance'])) {
                    $lines[] = 'ARGUMENT_GUIDANCE: '.json_encode($guidance['argument_guidance'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                }

                if (! empty($guidance['response_guidance'])) {
                    $lines[] = 'RESPONSE_GUIDANCE: '.json_encode(array_values($guidance['response_guidance']), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                }

                return implode("\n", $lines);
            })
            ->implode("\n\n");
    }

    private function formatSectorKnowledgeForPrompt(ChatbotProfile $profile, ConversationBehaviorProfile $behaviorProfile): string
    {
        if ($behaviorProfile->sectorKey !== 'winery') {
            return '';
        }

        $denominations = $this->inferDenominationsForBusiness($profile);
        $businessKnowledge = $denominations !== []
            ? 'DENOMINACIONES RELACIONABLES CON ESTE NEGOCIO POR UBICACION/DATOS PUBLICOS: '.implode(' | ', $denominations)
            : 'DENOMINACIONES RELACIONABLES CON ESTE NEGOCIO: si no puedes inferir una con razonable confianza a partir de la direccion o descripcion publica, no la inventes.';

        return <<<BLOCK
CONOCIMIENTO SECTORIAL EXTRA - BODEGA Y ENOLOGIA:
- Puedes responder dudas generales sobre vino, cata, maridaje, variedades y enoturismo sin necesidad de tool cuando la respuesta dependa de conocimiento sectorial general y del perfil del negocio.
- Adapta la profundidad tecnica al cliente: si habla en tono casual, responde claro y ameno; si pide detalle tecnico, puedes subir el nivel y explicar variedad, elaboracion, crianza, acidez, aromas, estructura y final.
- Para explicar una cata, usa una secuencia humana y simple: fase visual, nariz, boca, sensacion final y contexto del vino. No suenes academico salvo que el cliente lo busque.
- Para explicar una experiencia enoturistica ideal, piensa en acogida, contexto del lugar, recorrido, cata guiada, posible maridaje y ambiente del grupo.
- Cuando compares dos experiencias, habla de sensaciones, ambiente, qué incluye cada una y para qué tipo de visita encaja mejor cada propuesta.
- Puedes mencionar con naturalidad referencias solidas de denominaciones de origen espanolas como: Rias Baixas, Ribeiro, Ribeira Sacra, Valdeorras, Monterrei, Rioja, Ribera del Duero, Rueda, Toro, Bierzo, Priorat, Penedes, Cava, Jerez-Xeres-Sherry, Montilla-Moriles, La Mancha, Somontano, Utiel-Requena y Alicante.
- Si el cliente pregunta por la zona del negocio, conecta la experiencia con el territorio de forma natural, pero no afirmes una DO concreta si no esta respaldada por la ubicacion o las reglas del negocio.
- Si el cliente solo quiere reservar, no conviertas la conversacion en una masterclass de vino.
{$businessKnowledge}
BLOCK;
    }

    private function inferDenominationsForBusiness(ChatbotProfile $profile): array
    {
        $haystack = mb_strtolower(implode(' ', array_filter([
            $profile->negocioNombre,
            $profile->direccion,
            $profile->descripcionPublica,
            $profile->urlPublica,
            $profile->systemRules,
        ])), 'UTF-8');

        $maps = [
            [
                'keywords' => ['cambados', 'pontevedra', 'salnes', 'salnés', 'rias baixas', 'rías baixas', 'albariño', 'albarino'],
                'label' => 'D.O. Rias Baixas; en Cambados la referencia mas natural es Val do Salnes y la variedad emblematica es Albariño',
            ],
            [
                'keywords' => ['ribadavia', 'ribeiro', 'ourense'],
                'label' => 'D.O. Ribeiro',
            ],
            [
                'keywords' => ['ribeira sacra', 'chantada', 'monforte'],
                'label' => 'D.O. Ribeira Sacra',
            ],
            [
                'keywords' => ['valdeorras', 'o barco'],
                'label' => 'D.O. Valdeorras',
            ],
            [
                'keywords' => ['monterrei', 'verin', 'verín'],
                'label' => 'D.O. Monterrei',
            ],
            [
                'keywords' => ['rioja', 'haro', 'logroño', 'logrono'],
                'label' => 'D.O.Ca. Rioja',
            ],
            [
                'keywords' => ['ribera del duero', 'peñafiel', 'penafiel', 'valladolid', 'burgos'],
                'label' => 'D.O. Ribera del Duero',
            ],
            [
                'keywords' => ['rueda'],
                'label' => 'D.O. Rueda',
            ],
            [
                'keywords' => ['toro', 'zamora'],
                'label' => 'D.O. Toro',
            ],
            [
                'keywords' => ['bierzo', 'ponferrada'],
                'label' => 'D.O. Bierzo',
            ],
            [
                'keywords' => ['priorat'],
                'label' => 'D.O.Q. Priorat',
            ],
            [
                'keywords' => ['penedes', 'penedès', 'sant sadurni', 'sant sadurní', 'cava'],
                'label' => 'D.O. Penedes / D.O. Cava',
            ],
            [
                'keywords' => ['jerez', 'sanlucar', 'sanlúcar', 'el puerto de santa maria'],
                'label' => 'D.O. Jerez-Xeres-Sherry / Manzanilla-Sanlucar de Barrameda',
            ],
            [
                'keywords' => ['montilla', 'moriles'],
                'label' => 'D.O. Montilla-Moriles',
            ],
            [
                'keywords' => ['la mancha', 'valdepeñas', 'valdepenas'],
                'label' => 'D.O. La Mancha / D.O. Valdepenas',
            ],
            [
                'keywords' => ['somontano', 'barbastro'],
                'label' => 'D.O. Somontano',
            ],
            [
                'keywords' => ['utiel', 'requena'],
                'label' => 'D.O. Utiel-Requena',
            ],
            [
                'keywords' => ['alicante'],
                'label' => 'D.O. Alicante',
            ],
        ];

        $matches = [];

        foreach ($maps as $map) {
            foreach ($map['keywords'] as $keyword) {
                if (str_contains($haystack, $keyword)) {
                    $matches[] = $map['label'];
                    break;
                }
            }
        }

        return array_values(array_unique($matches));
    }
}
