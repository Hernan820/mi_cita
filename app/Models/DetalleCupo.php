<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class DetalleCupo
 * 
 * @property int $id
 * @property bool|null $estado_cupo
 * @property bool|null $confirmacion
 * @property Carbon $hora
 * @property string $descripcion
 * @property string|null $motivo_cancelacion
 * @property int|null $id_estado
 * @property int $id_cupo
 * @property int $id_cliente
 * @property int $id_usuario
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Cliente $cliente
 * @property Cupo $cupo
 * @property Estado|null $estado
 * @property User $user
 * @property Collection|Nota[] $notas
 *
 * @package App\Models
 */
class DetalleCupo extends Model
{
	protected $table = 'detalle_cupos';

	protected $casts = [
		'estado_cupo' => 'bool',
		'confirmacion' => 'bool',
		'id_estado' => 'int',
		'id_cupo' => 'int',
		'id_cliente' => 'int',
		'id_usuario' => 'int'
	];

	protected $dates = [
		'hora'
	];

	protected $fillable = [
		'estado_cupo',
		'confirmacion',
		'hora',
		'descripcion',
		'motivo_cancelacion',
		'id_estado',
		'id_cupo',
		'id_cliente',
		'id_usuario'
	];

	public function cliente()
	{
		return $this->belongsTo(Cliente::class, 'id_cliente');
	}

	public function cupo()
	{
		return $this->belongsTo(Cupo::class, 'id_cupo');
	}

	public function estado()
	{
		return $this->belongsTo(Estado::class, 'id_estado');
	}

	public function user()
	{
		return $this->belongsTo(User::class, 'id_usuario');
	}

	public function notas()
	{
		return $this->hasMany(Nota::class, 'id_cita');
	}
}
