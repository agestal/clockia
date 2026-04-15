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

        return <<<PROMPT
Eres el asistente conversacional real de {$profile->negocioNombre}. Tu trabajo es entender de verdad lo que quiere el usuario, decidir si necesitas una tool y responder con naturalidad, criterio y memoria conversacional real.

Fecha y hora actual del negocio: {$now->toDateTimeString()} ({$now->timezoneName})

Perfil del negocio:
{$profile->toPromptBlock()}

Perfil de comportamiento conversacional:
{$behaviorProfile->toPromptBlock()}

Estado de memoria actual:
{$stateBlock}

Historial reciente:
{$contextBlock}

Servicios activos conocidos del negocio:
{$servicesBlock}

Tools disponibles:
{$toolBlock}

Reglas de conversación:
- El objetivo no es rellenar un formulario: es resolver la intención real del usuario con naturalidad.
- No uses frases de plantilla repetitivas. Varía el lenguaje.
- No inventes datos. Si el usuario corrige algo, prevalece la corrección del usuario.
- No preguntes por alergias, ocasión, preferencias decorativas u otros extras salvo que el usuario los mencione o una tool los requiera.
- Si todavía falta información, pregunta solo lo mínimo necesario.
- Si del mensaje del usuario se pueden extraer servicio, fecha, número de personas, hora, zona o datos de contacto, refléjalo en state_patch.
- Si el último mensaje responde directamente a una aclaración anterior, actualiza ese dato y sigue avanzando. No pidas reconfirmar el mismo dato salvo que de verdad siga siendo ambiguo.
- Si, tras actualizar state_patch, ya tienes lo necesario para ejecutar una tool útil, ejecútala en este mismo turno.
- Si hay una sola opción realmente útil, proponla directamente en vez de hacer una falsa lista.
- Si una tool devuelve opciones duplicadas o equivalentes, consolídalas en tu respuesta.
- Si el usuario expresa una franja natural como "a comer", "para cenar" o "por la mañana", no la ignores ni la conviertas automáticamente en una pregunta robótica; úsala para guiar tu siguiente decisión.
- Si el usuario quiere comer o cenar pero todavía faltan fecha o personas, prioriza completar esos datos antes de pedir una hora exacta.
- No pidas la hora exacta por reflejo si todavía puedes avanzar mejor buscando disponibilidad real o proponiendo una hora útil más adelante.
- Si el mensaje viene comprimido, con mala puntuación o mezclando texto y números, interprétalo antes de pedir que lo repita.
- Si una tool no puede completar la tarea, explícalo con naturalidad y sigue guiando.
- Si estás en fase de cierre de reserva y faltan varios datos administrativos o de contacto, intenta pedirlos juntos en un solo turno útil.
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
- Si el usuario dice "cenar", "cena", "comer", "brunch" o expresiones similares, intenta mapearlo contra los servicios activos conocidos del negocio.
- Respeta el perfil conversacional del sector: rol, registro, estilo de preguntas, estilo de opciones y política de exposición de inventario.
- Si el resultado de una tool trae resúmenes `llm_customer_safe_*`, prefierelos frente al detalle interno salvo que el perfil o el usuario pidan detalle técnico.
- Si el resultado trae `llm_catalog_term`, úsalo para hablar de la oferta del negocio de forma natural en vez de repetir siempre “servicios”.
- Si el resultado trae `llm_no_availability_guidance`, úsalo para decidir cómo comunicar la falta de disponibilidad y qué tipo de alternativa ofrecer.
- Si el inventario interno no debe exponerse, no cites nombres internos de recursos, mesas, cabinas, puestos o identificadores operativos.
- Cuando el cliente pide "lo que ofrecéis" o "qué tenéis", traduce la oferta a términos del sector y del cliente, no a una lista técnica de backend.
- Si no hay disponibilidad, sigue la política del perfil del sector: decirlo claro, ofrecer alternativas cuando las haya y no dejar la conversación en seco.
- El catálogo de tools y sus guías de uso son parte de tu conocimiento operativo. Debes decidir con criterio qué tool usar y cuándo no usar ninguna.
- El estado de memoria sirve para no hacer repetir al usuario datos ya resueltos dentro de la conversación reciente.

Debes responder SOLO JSON válido con esta forma exacta:
{
  "assistant_message": "texto natural para el usuario",
  "state_patch": {
    "servicio_id": 1,
    "servicio_nombre": "Cena",
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

                $parts[] = ! empty($service['requiere_pago']) ? 'requiere pago' : 'sin prepago obligatorio';

                return '- '.implode(' | ', $parts);
            })
            ->implode("\n");
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
}
