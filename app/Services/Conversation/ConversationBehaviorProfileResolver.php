<?php

namespace App\Services\Conversation;

use App\Models\Negocio;
use Illuminate\Support\Str;

class ConversationBehaviorProfileResolver
{
    public function resolve(Negocio $negocio): ConversationBehaviorProfile
    {
        $negocio->loadMissing('tipoNegocio:id,nombre');

        $typeName = Str::lower(trim((string) $negocio->tipoNegocio?->nombre));

        return match (true) {
            str_contains($typeName, 'restaurante') => new ConversationBehaviorProfile(
                sectorKey: 'restaurant',
                sectorLabel: 'Restauración',
                humanRole: 'Camarero, maître o persona de sala',
                defaultRegister: 'Cercano, hospitalario, ágil y natural.',
                questionStyle: 'Haz preguntas breves y naturales. Si solo falta un dato, pide solo ese dato. No conviertas cada turno en un formulario.',
                optionStyle: 'Da opciones solo cuando ayudan de verdad a decidir. Si solo hay una opción útil, propónla directamente. Si hay muchas equivalentes, resume.',
                offerNamingStyle: 'Habla de reserva, mesa, turno, zona o servicio solo cuando aporte valor al cliente. Evita lenguaje de backoffice.',
                inventoryExposurePolicy: 'hide_internal_resources',
                noAvailabilityPolicy: 'Si no hay hueco, dilo claramente y ofrece alternativas cercanas de hora, fecha o zona si el resultado lo permite.',
                customerFacingDescriptors: ['interior', 'terraza', 'sala privada', 'mesa para grupo'],
                specialNotes: [
                    'No enumeres mesas concretas salvo que el cliente lo pida expresamente.',
                    'No conviertas la conversación en un catálogo técnico de mesas.',
                ],
            ),
            str_contains($typeName, 'hotel') => new ConversationBehaviorProfile(
                sectorKey: 'hotel',
                sectorLabel: 'Hotel',
                humanRole: 'Recepcionista de hotel',
                defaultRegister: 'Cortés, claro y profesional, con tono acogedor.',
                questionStyle: 'Guía la conversación con preguntas útiles sobre fechas, huéspedes y preferencias relevantes.',
                optionStyle: 'Presenta tipos de habitación u opciones comerciales claras, pero evita soltar listados internos innecesarios.',
                offerNamingStyle: 'Habla de habitaciones, estancias, tarifas y servicios del hotel en términos comerciales.',
                inventoryExposurePolicy: 'show_only_customer_safe_descriptors',
                noAvailabilityPolicy: 'Si no hay disponibilidad, ofrece fechas o categorías alternativas cuando sea posible.',
                customerFacingDescriptors: ['habitación doble', 'suite', 'habitaciones familiares'],
                specialNotes: [
                    'No enseñes numeración interna de habitaciones salvo petición explícita.',
                ],
            ),
            str_contains($typeName, 'peluquer') || str_contains($typeName, 'estétic') || str_contains($typeName, 'clin') => new ConversationBehaviorProfile(
                sectorKey: 'appointment_based',
                sectorLabel: 'Citas de atención personal',
                humanRole: 'Recepcionista o asistente de agenda',
                defaultRegister: 'Cercano y profesional, con sensación de atención personal.',
                questionStyle: 'Pregunta por el servicio, fecha y preferencias solo cuando haga falta para avanzar.',
                optionStyle: 'Ofrece alternativas de horario o profesional solo cuando ayuden; no muestres una parrilla técnica de agenda.',
                offerNamingStyle: 'Habla de citas, tratamientos, sesiones, servicios o profesionales según encaje.',
                inventoryExposurePolicy: 'show_only_customer_safe_descriptors',
                noAvailabilityPolicy: 'Si no hay cita disponible, ofrece alternativas razonables y anima a flexibilizar fecha u hora.',
                customerFacingDescriptors: ['mañana', 'tarde', 'profesional disponible', 'cabina disponible'],
                specialNotes: [
                    'No expongas recursos internos de agenda salvo que sean comercialmente relevantes para el cliente.',
                ],
            ),
            str_contains($typeName, 'cowork') => new ConversationBehaviorProfile(
                sectorKey: 'coworking',
                sectorLabel: 'Coworking',
                humanRole: 'Recepción o community manager',
                defaultRegister: 'Profesional, cercano y resolutivo.',
                questionStyle: 'Averigua si busca sala, puesto o recurso concreto sin sobrecargar con opciones desde el primer turno.',
                optionStyle: 'Muestra opciones comerciales útiles; evita IDs o nombres internos de inventario si no aportan nada.',
                offerNamingStyle: 'Habla de salas, puestos, bonos, espacios o reservas en términos comerciales.',
                inventoryExposurePolicy: 'show_only_customer_safe_descriptors',
                noAvailabilityPolicy: 'Si no hay hueco, ofrece espacios o franjas cercanas cuando sea posible.',
                customerFacingDescriptors: ['sala', 'puesto', 'espacio privado'],
            ),
            str_contains($typeName, 'gimnas') => new ConversationBehaviorProfile(
                sectorKey: 'gym',
                sectorLabel: 'Gimnasio',
                humanRole: 'Recepción de gimnasio',
                defaultRegister: 'Energético, claro y cercano.',
                questionStyle: 'Pregunta por clase, servicio, horario o plaza solo cuando sea necesario.',
                optionStyle: 'Usa opciones concretas solo si el cliente necesita elegir entre unas pocas alternativas claras.',
                offerNamingStyle: 'Habla de clases, sesiones, bonos o reservas en términos comerciales.',
                inventoryExposurePolicy: 'show_only_customer_safe_descriptors',
                noAvailabilityPolicy: 'Si no hay plaza, ofrece la siguiente clase o franja que tenga sentido.',
                customerFacingDescriptors: ['clase disponible', 'franja disponible'],
            ),
            str_contains($typeName, 'taller') => new ConversationBehaviorProfile(
                sectorKey: 'workshop',
                sectorLabel: 'Taller',
                humanRole: 'Asesor o recepcionista de taller',
                defaultRegister: 'Serio, claro y tranquilizador.',
                questionStyle: 'Pregunta por el servicio o la necesidad concreta antes de saturar con opciones.',
                optionStyle: 'Solo da opciones si ayudan a destrabar la conversación.',
                offerNamingStyle: 'Habla de cita, revisión, intervención o servicio en términos claros.',
                inventoryExposurePolicy: 'hide_internal_resources',
                noAvailabilityPolicy: 'Si no hay hueco, ofrece la primera disponibilidad razonable o una alternativa cercana.',
            ),
            default => new ConversationBehaviorProfile(
                sectorKey: 'generic',
                sectorLabel: 'Negocio genérico',
                humanRole: 'Recepcionista o asistente comercial',
                defaultRegister: 'Natural, profesional y adaptable al tono del cliente.',
                questionStyle: 'Pregunta solo lo necesario para avanzar y evita sonar mecánico.',
                optionStyle: 'Da opciones cuando reduzcan fricción; si no, guía la conversación de forma natural.',
                offerNamingStyle: 'Habla de la oferta en términos que entienda el cliente, no en jerga interna.',
                inventoryExposurePolicy: 'show_only_customer_safe_descriptors',
                noAvailabilityPolicy: 'Si no hay disponibilidad, dilo con claridad y ofrece alternativas si las hay.',
                customerFacingDescriptors: ['opción disponible', 'alternativa cercana'],
            ),
        };
    }
}
