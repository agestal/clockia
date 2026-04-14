<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->string('nombre_responsable')->nullable()->after('cliente_id');
            $table->string('email_responsable')->nullable()->after('nombre_responsable');
            $table->string('telefono_responsable')->nullable()->after('email_responsable');
            $table->string('tipo_documento_responsable', 100)->nullable()->after('telefono_responsable');
            $table->string('documento_responsable', 255)->nullable()->after('tipo_documento_responsable');
        });

        $rows = DB::table('reservas')
            ->join('clientes', 'clientes.id', '=', 'reservas.cliente_id')
            ->select('reservas.id', 'clientes.nombre', 'clientes.email', 'clientes.telefono')
            ->orderBy('reservas.id')
            ->get();

        foreach ($rows as $row) {
            DB::table('reservas')
                ->where('id', $row->id)
                ->update([
                    'nombre_responsable' => $row->nombre,
                    'email_responsable' => $row->email,
                    'telefono_responsable' => $row->telefono,
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->dropColumn([
                'nombre_responsable',
                'email_responsable',
                'telefono_responsable',
                'tipo_documento_responsable',
                'documento_responsable',
            ]);
        });
    }
};
