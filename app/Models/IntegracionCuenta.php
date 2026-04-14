<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $integracion_id
 * @property string|null $cuenta_externa_id
 * @property string|null $email_externo
 * @property string|null $nombre_externo
 * @property string|null $access_token
 * @property string|null $refresh_token
 * @property \Illuminate\Support\Carbon|null $token_expira_en
 * @property string|null $scopes
 * @property array|null $datos_extra
 * @property bool $activo
 * @property-read Integracion $integracion
 */
class IntegracionCuenta extends Model
{
    use HasFactory;

    protected $table = 'integracion_cuentas';

    protected $fillable = [
        'integracion_id',
        'cuenta_externa_id',
        'email_externo',
        'nombre_externo',
        'access_token',
        'refresh_token',
        'token_expira_en',
        'scopes',
        'datos_extra',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'integracion_id' => 'integer',
            'token_expira_en' => 'datetime',
            'datos_extra' => 'array',
            'activo' => 'boolean',
            'access_token' => 'encrypted',
            'refresh_token' => 'encrypted',
        ];
    }

    public function integracion(): BelongsTo
    {
        return $this->belongsTo(Integracion::class);
    }
}
