<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Solicitude
 * 
 * @property int $id
 * @property int $loan_officer
 * @property string|null $fecha
 * @property string|null $estatus_legal
 * @property string|null $lugar_trabajo
 * @property string|null $tiempo_trabajo
 * @property string|null $empleador
 * @property string|null $salario_semanal
 * @property string|null $tipo_trabajo
 * @property int|null $taxes
 * @property string|null $cantidad_taxes
 * @property string|null $canal
 * @property int|null $estado_solicitud
 * @property string|null $cant_prestamo
 * @property string|null $state
 * @property int|null $agente_bienes
 * @property string|null $comentario
 * @property string|null $nomb_agente
 * @property string|null $fecha_contrato
 * @property int|null $id_prestamo
 * @property int|null $id_procesador
 * @property string|null $notas_solicitud
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Prestamo|null $prestamo
 * @property Procesadore|null $procesadore
 * @property User $user
 * @property Collection|Archivo[] $archivos
 * @property Collection|Cliente[] $clientes
 *
 * @package App\Models
 */
class Solicitude extends Model
{
	protected $table = 'solicitudes';

	protected $casts = [
		'loan_officer' => 'int',
		'taxes' => 'int',
		'estado_solicitud' => 'int',
		'agente_bienes' => 'int',
		'id_prestamo' => 'int',
		'id_procesador' => 'int'
	];

	protected $fillable = [
		'loan_officer',
		'fecha',
		'estatus_legal',
		'lugar_trabajo',
		'tiempo_trabajo',
		'empleador',
		'salario_semanal',
		'tipo_trabajo',
		'taxes',
		'cantidad_taxes',
		'canal',
		'estado_solicitud',
		'cant_prestamo',
		'state',
		'agente_bienes',
		'comentario',
		'nomb_agente',
		'fecha_contrato',
		'id_prestamo',
		'id_procesador',
		'notas_solicitud'
	];

	public function prestamo()
	{
		return $this->belongsTo(Prestamo::class, 'id_prestamo');
	}

	public function procesadore()
	{
		return $this->belongsTo(Procesadore::class, 'id_procesador');
	}

	public function user()
	{
		return $this->belongsTo(User::class, 'loan_officer');
	}

	public function archivos()
	{
		return $this->hasMany(Archivo::class, 'id_solicitud');
	}

	public function clientes()
	{
		return $this->belongsToMany(Cliente::class, 'cliente_solicitudes', 'id_solicitud', 'id_cliente')
					->withPivot('id')
					->withTimestamps();
	}
}
