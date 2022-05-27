<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Cliente
 * 
 * @property int $id
 * @property string $nombre
 * @property string $apellidos
 * @property string $direccion
 * @property string $correo
 * @property string|null $telefono_fijo
 * @property string $telefono
 * @property int|null $estado_cliente
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Collection|Solicitude[] $solicitudes
 * @property Collection|DetalleCupo[] $detalle_cupos
 *
 * @package App\Models
 */
class Cliente extends Model
{
	protected $table = 'clientes';

	protected $casts = [
		'estado_cliente' => 'int'
	];

	protected $fillable = [
		'nombre',
		'apellidos',
		'direccion',
		'correo',
		'telefono_fijo',
		'telefono',
		'estado_cliente'
	];

	public function solicitudes()
	{
		return $this->belongsToMany(Solicitude::class, 'cliente_solicitudes', 'id_cliente', 'id_solicitud')
					->withPivot('id')
					->withTimestamps();
	}

	public function detalle_cupos()
	{
		return $this->hasMany(DetalleCupo::class, 'id_cliente');
	}
}
