<?php

namespace App\Console\Commands;

use App\Jobs\EnviarMailEncuesta;
use App\Jobs\EnviarMailRecordatorio;
use App\Models\Negocio;
use App\Models\Reserva;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ProcesarMailsReservas extends Command
{
    protected $signature = 'clockia:procesar-mails';

    protected $description = 'Procesa recordatorios y encuestas de satisfacción pendientes de enviar';

    public function handle(): int
    {
        $this->procesarRecordatorios();
        $this->procesarEncuestas();

        return self::SUCCESS;
    }

    private function procesarRecordatorios(): void
    {
        $negocios = Negocio::where('mail_recordatorio_activo', true)->get();
        $enviados = 0;

        foreach ($negocios as $negocio) {
            $horasAntes = $negocio->mail_recordatorio_horas_antes ?? 24;
            $ahora = Carbon::now($negocio->zona_horaria ?? 'Europe/Madrid');

            // Find reservas that start within the reminder window and haven't been reminded yet
            $limiteInicio = $ahora->copy();
            $limiteFin = $ahora->copy()->addHours($horasAntes);

            $reservas = Reserva::where('negocio_id', $negocio->id)
                ->whereNull('mail_recordatorio_enviado_en')
                ->whereNotNull('email_responsable')
                ->whereNotNull('inicio_datetime')
                ->where('inicio_datetime', '>=', $limiteInicio)
                ->where('inicio_datetime', '<=', $limiteFin)
                ->whereHas('estadoReserva', fn ($q) => $q->whereIn('nombre', ['Confirmada', 'Pendiente']))
                ->get();

            foreach ($reservas as $reserva) {
                EnviarMailRecordatorio::dispatch($reserva);
                $enviados++;
            }
        }

        if ($enviados > 0) {
            $this->info("Recordatorios despachados: {$enviados}");
        }
    }

    private function procesarEncuestas(): void
    {
        $negocios = Negocio::where('mail_encuesta_activo', true)->get();
        $enviados = 0;

        foreach ($negocios as $negocio) {
            $horasDespues = $negocio->mail_encuesta_horas_despues ?? 24;
            $ahora = Carbon::now($negocio->zona_horaria ?? 'Europe/Madrid');

            // Find reservas that ended more than X hours ago and haven't been surveyed yet
            $limiteFin = $ahora->copy()->subHours($horasDespues);

            $reservas = Reserva::where('negocio_id', $negocio->id)
                ->whereNull('mail_encuesta_enviado_en')
                ->whereNotNull('email_responsable')
                ->whereNotNull('fin_datetime')
                ->where('fin_datetime', '<=', $limiteFin)
                ->whereHas('estadoReserva', fn ($q) => $q->whereIn('nombre', ['Completada']))
                ->get();

            foreach ($reservas as $reserva) {
                EnviarMailEncuesta::dispatch($reserva);
                $enviados++;
            }
        }

        if ($enviados > 0) {
            $this->info("Encuestas despachadas: {$enviados}");
        }
    }
}
