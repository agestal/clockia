<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plantillas_email', function (Blueprint $table) {
            $table->id();
            $table->foreignId('negocio_id')->constrained('negocios')->cascadeOnDelete();
            $table->string('tipo', 40);
            $table->string('asunto')->nullable();
            $table->string('titulo')->nullable();
            $table->string('saludo')->nullable();
            $table->text('introduccion')->nullable();
            $table->text('cuerpo')->nullable();
            $table->string('texto_boton')->nullable();
            $table->text('texto_pie')->nullable();
            $table->string('color_primario', 20)->nullable();
            $table->string('color_boton', 20)->nullable();
            $table->string('color_fondo', 20)->nullable();
            $table->string('color_texto', 20)->nullable();
            $table->timestamps();

            $table->unique(['negocio_id', 'tipo']);
        });

        Schema::create('encuesta_plantillas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('negocio_id')->constrained('negocios')->cascadeOnDelete();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->boolean('predeterminada')->default(false);
            $table->unsignedTinyInteger('escala_min')->default(0);
            $table->unsignedTinyInteger('escala_max')->default(10);
            $table->boolean('permite_comentario_final')->default(true);
            $table->string('comentario_placeholder')->nullable();
            $table->string('titulo_publico')->nullable();
            $table->text('intro_publica')->nullable();
            $table->string('agradecimiento_titulo')->nullable();
            $table->text('agradecimiento_texto')->nullable();
            $table->timestamps();

            $table->index(['negocio_id', 'activo']);
        });

        Schema::table('encuestas', function (Blueprint $table) {
            $table->foreignId('encuesta_plantilla_id')
                ->nullable()
                ->after('negocio_id')
                ->constrained('encuesta_plantillas');
            $table->boolean('activo')->default(true)->after('token');
            $table->json('contenido_snapshot')->nullable()->after('comentario_general');
            $table->index(['negocio_id', 'activo']);
        });

        Schema::table('encuesta_items', function (Blueprint $table) {
            $table->foreignId('encuesta_plantilla_id')
                ->nullable()
                ->after('negocio_id')
                ->constrained('encuesta_plantillas');
            $table->index(['encuesta_plantilla_id', 'activo']);
        });

        $negocios = DB::table('negocios')->select('id')->orderBy('id')->get();
        $now = now();

        foreach ($negocios as $negocio) {
            $plantillaId = DB::table('encuesta_plantillas')->insertGetId([
                'negocio_id' => $negocio->id,
                'nombre' => 'Encuesta post-experiencia',
                'descripcion' => 'Plantilla inicial para recoger valoraciones tras la reserva.',
                'activo' => true,
                'predeterminada' => true,
                'escala_min' => 0,
                'escala_max' => 10,
                'permite_comentario_final' => true,
                'comentario_placeholder' => 'Si quieres, cuentanos algun detalle de tu visita.',
                'titulo_publico' => 'Comparte tu valoracion',
                'intro_publica' => 'Nos ayuda mucho saber como ha ido la experiencia.',
                'agradecimiento_titulo' => 'Gracias por tu valoracion',
                'agradecimiento_texto' => 'Tu opinion nos ayuda a mejorar cada visita.',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('encuesta_items')
                ->where('negocio_id', $negocio->id)
                ->update([
                    'encuesta_plantilla_id' => $plantillaId,
                    'updated_at' => $now,
                ]);

            $hasItems = DB::table('encuesta_items')
                ->where('encuesta_plantilla_id', $plantillaId)
                ->exists();

            if (! $hasItems) {
                DB::table('encuesta_items')->insert([
                    'negocio_id' => $negocio->id,
                    'encuesta_plantilla_id' => $plantillaId,
                    'clave' => "tpl_{$plantillaId}_1_valoracion_general",
                    'etiqueta' => 'Valoracion general de la experiencia',
                    'descripcion' => '¿Como valorarias tu visita en general?',
                    'orden' => 1,
                    'activo' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            foreach (['confirmacion', 'recordatorio', 'encuesta'] as $tipo) {
                DB::table('plantillas_email')->insert([
                    'negocio_id' => $negocio->id,
                    'tipo' => $tipo,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        DB::table('encuestas')
            ->whereNull('encuesta_plantilla_id')
            ->update([
                'encuesta_plantilla_id' => DB::raw('(select encuesta_plantillas.id from encuesta_plantillas where encuesta_plantillas.negocio_id = encuestas.negocio_id order by encuesta_plantillas.predeterminada desc, encuesta_plantillas.id asc limit 1)'),
            ]);

        DB::table('encuestas')
            ->update([
                'activo' => DB::raw('case when respondida_en is null then 1 else 0 end'),
            ]);
    }

    public function down(): void
    {
        Schema::table('encuesta_items', function (Blueprint $table) {
            $table->dropIndex('encuesta_items_encuesta_plantilla_id_activo_index');
            $table->dropConstrainedForeignId('encuesta_plantilla_id');
        });

        Schema::table('encuestas', function (Blueprint $table) {
            $table->dropIndex('encuestas_negocio_id_activo_index');
            $table->dropConstrainedForeignId('encuesta_plantilla_id');
            $table->dropColumn(['activo', 'contenido_snapshot']);
        });

        Schema::dropIfExists('encuesta_plantillas');
        Schema::dropIfExists('plantillas_email');
    }
};
