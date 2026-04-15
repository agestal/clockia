<?php

use App\Http\Controllers\Admin\TipoNegocioController;
use App\Http\Controllers\Admin\TipoPrecioController;
use App\Http\Controllers\Admin\TipoRecursoController;
use App\Http\Controllers\Admin\TipoBloqueoController;
use App\Http\Controllers\Admin\EstadoReservaController;
use App\Http\Controllers\Admin\TipoPagoController;
use App\Http\Controllers\Admin\EstadoPagoController;
use App\Http\Controllers\Admin\ClienteController;
use App\Http\Controllers\Admin\NegocioController;
use App\Http\Controllers\Admin\RecursoController;
use App\Http\Controllers\Admin\ReservaController;
use App\Http\Controllers\Admin\ServicioController;
use App\Http\Controllers\Admin\PagoController;
use App\Http\Controllers\Admin\DisponibilidadController;
use App\Http\Controllers\Admin\BloqueoController;
use App\Http\Controllers\Admin\ConceptoPagoController;
use App\Http\Controllers\Admin\RecursoCombinacionController;
use App\Http\Controllers\Admin\IntegracionController;
use App\Http\Controllers\Admin\IntegracionMapeoController;
use App\Http\Controllers\Admin\OcupacionExternaController;
use App\Http\Controllers\Admin\CalendarioController;
use App\Http\Controllers\Admin\ChatTestController;
use App\Http\Controllers\EncuestaPublicaController;
use App\Livewire\Admin\Dashboard;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::get('/encuesta/{token}', [EncuestaPublicaController::class, 'show'])->name('encuesta.show');
Route::post('/encuesta/{token}', [EncuestaPublicaController::class, 'submit'])->name('encuesta.submit');

Route::middleware([
    'auth',
    config('jetstream.auth_session'),
])->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
});

Route::prefix('admin')
    ->name('admin.')
    ->middleware([
        'auth',
        config('jetstream.auth_session'),
        'verified',
    ])
    ->group(function () {
        Route::resource('tipos-negocio', TipoNegocioController::class)
            ->parameters(['tipos-negocio' => 'tipo_negocio']);
        Route::resource('tipos-precio', TipoPrecioController::class)
            ->parameters(['tipos-precio' => 'tipo_precio']);
        Route::resource('tipos-recurso', TipoRecursoController::class)
            ->parameters(['tipos-recurso' => 'tipo_recurso']);
        Route::resource('tipos-bloqueo', TipoBloqueoController::class)
            ->parameters(['tipos-bloqueo' => 'tipo_bloqueo']);
        Route::resource('estados-reserva', EstadoReservaController::class)
            ->parameters(['estados-reserva' => 'estado_reserva']);
        Route::resource('tipos-pago', TipoPagoController::class)
            ->parameters(['tipos-pago' => 'tipo_pago']);
        Route::resource('estados-pago', EstadoPagoController::class)
            ->parameters(['estados-pago' => 'estado_pago']);
        Route::resource('conceptos-pago', ConceptoPagoController::class)
            ->parameters(['conceptos-pago' => 'concepto_pago']);
        Route::resource('clientes', ClienteController::class)
            ->parameters(['clientes' => 'cliente']);
        Route::resource('negocios', NegocioController::class)
            ->parameters(['negocios' => 'negocio']);
        Route::resource('servicios', ServicioController::class)
            ->parameters(['servicios' => 'servicio']);
        Route::resource('reservas', ReservaController::class)
            ->parameters(['reservas' => 'reserva']);
        Route::resource('pagos', PagoController::class)
            ->parameters(['pagos' => 'pago']);
        Route::resource('recursos', RecursoController::class)
            ->parameters(['recursos' => 'recurso']);
        Route::resource('disponibilidades', DisponibilidadController::class)
            ->parameters(['disponibilidades' => 'disponibilidad']);
        Route::resource('bloqueos', BloqueoController::class)
            ->parameters(['bloqueos' => 'bloqueo']);
        Route::resource('recurso-combinaciones', RecursoCombinacionController::class)
            ->parameters(['recurso-combinaciones' => 'recurso_combinacion']);
        Route::resource('integraciones', IntegracionController::class)
            ->parameters(['integraciones' => 'integracion']);
        Route::resource('integracion-mapeos', IntegracionMapeoController::class)
            ->parameters(['integracion-mapeos' => 'integracion_mapeo']);
        Route::resource('ocupaciones-externas', OcupacionExternaController::class)
            ->parameters(['ocupaciones-externas' => 'ocupacion_externa']);

        Route::patch('tipos-negocio/{tipo_negocio}/inline', [TipoNegocioController::class, 'inlineUpdate'])
            ->name('tipos-negocio.inline-update');
        Route::patch('tipos-precio/{tipo_precio}/inline', [TipoPrecioController::class, 'inlineUpdate'])
            ->name('tipos-precio.inline-update');
        Route::patch('tipos-recurso/{tipo_recurso}/inline', [TipoRecursoController::class, 'inlineUpdate'])
            ->name('tipos-recurso.inline-update');
        Route::patch('tipos-bloqueo/{tipo_bloqueo}/inline', [TipoBloqueoController::class, 'inlineUpdate'])
            ->name('tipos-bloqueo.inline-update');
        Route::patch('estados-reserva/{estado_reserva}/inline', [EstadoReservaController::class, 'inlineUpdate'])
            ->name('estados-reserva.inline-update');
        Route::patch('tipos-pago/{tipo_pago}/inline', [TipoPagoController::class, 'inlineUpdate'])
            ->name('tipos-pago.inline-update');
        Route::patch('estados-pago/{estado_pago}/inline', [EstadoPagoController::class, 'inlineUpdate'])
            ->name('estados-pago.inline-update');
        Route::patch('conceptos-pago/{concepto_pago}/inline', [ConceptoPagoController::class, 'inlineUpdate'])
            ->name('conceptos-pago.inline-update');
        Route::patch('clientes/{cliente}/inline', [ClienteController::class, 'inlineUpdate'])
            ->name('clientes.inline-update');
        Route::patch('negocios/{negocio}/inline', [NegocioController::class, 'inlineUpdate'])
            ->name('negocios.inline-update');
        Route::patch('servicios/{servicio}/inline', [ServicioController::class, 'inlineUpdate'])
            ->name('servicios.inline-update');
        Route::patch('reservas/{reserva}/inline', [ReservaController::class, 'inlineUpdate'])
            ->name('reservas.inline-update');
        Route::patch('pagos/{pago}/inline', [PagoController::class, 'inlineUpdate'])
            ->name('pagos.inline-update');
        Route::patch('recursos/{recurso}/inline', [RecursoController::class, 'inlineUpdate'])
            ->name('recursos.inline-update');
        Route::patch('disponibilidades/{disponibilidad}/inline', [DisponibilidadController::class, 'inlineUpdate'])
            ->name('disponibilidades.inline-update');

        Route::prefix('ajax')->name('ajax.')->group(function () {
            Route::get('tipos-negocio', [TipoNegocioController::class, 'searchOptions'])
                ->name('tipos-negocio.search');
            Route::get('tipos-precio', [TipoPrecioController::class, 'searchOptions'])
                ->name('tipos-precio.search');
            Route::get('tipos-recurso', [TipoRecursoController::class, 'searchOptions'])
                ->name('tipos-recurso.search');
            Route::get('tipos-bloqueo', [TipoBloqueoController::class, 'searchOptions'])
                ->name('tipos-bloqueo.search');
            Route::get('estados-reserva', [EstadoReservaController::class, 'searchOptions'])
                ->name('estados-reserva.search');
            Route::get('tipos-pago', [TipoPagoController::class, 'searchOptions'])
                ->name('tipos-pago.search');
            Route::get('estados-pago', [EstadoPagoController::class, 'searchOptions'])
                ->name('estados-pago.search');
            Route::get('conceptos-pago', [ConceptoPagoController::class, 'searchOptions'])
                ->name('conceptos-pago.search');
            Route::get('integraciones', [IntegracionController::class, 'searchOptions'])
                ->name('integraciones.search');
            Route::get('clientes', [ClienteController::class, 'searchOptions'])
                ->name('clientes.search');
            Route::get('negocios', [NegocioController::class, 'searchOptions'])
                ->name('negocios.search');
            Route::get('servicios', [ServicioController::class, 'searchOptions'])
                ->name('servicios.search');
            Route::get('pagos', [PagoController::class, 'searchOptions'])
                ->name('pagos.search');
            Route::get('reservas', [ReservaController::class, 'searchOptions'])
                ->name('reservas.search');
            Route::get('recursos', [RecursoController::class, 'searchOptions'])
                ->name('recursos.search');
        });

        Route::get('calendario', [CalendarioController::class, 'index'])->name('calendario.index');
        Route::get('calendario/data', [CalendarioController::class, 'data'])->name('calendario.data');
        Route::get('calendario/day', [CalendarioController::class, 'dayDetail'])->name('calendario.day');

        Route::get('chat/test', [ChatTestController::class, 'index'])
            ->name('chat-test.index');
        Route::post('chat/test', [ChatTestController::class, 'execute'])
            ->name('chat-test.execute');
        Route::delete('chat/test/context', [ChatTestController::class, 'clearContext'])
            ->name('chat-test.clear-context');
    });
