<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Cupo
 * 
 * @property int $id
 * @property Carbon $start
 * @property Carbon $end
 * @property int $id_oficina
 * @property int|null $estado_cupo
 * @property int|null $cant_citas
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Oficina $oficina
 * @property Collection|DetalleCupo[] $detalle_cupos
 *
 * @package App\Models
 */
class Cupo extends Model
{
	protected $table = 'cupos';

	protected $casts = [
		'id_oficina' => 'int',
		'estado_cupo' => 'int',
		'cant_citas' => 'int'
	];

	protected $dates = [
		'start',
		'end'
	];

	protected $fillable = [
		'start',
		'end',
		'id_oficina',
		'estado_cupo',
		'cant_citas'
	];

	public function oficina()
	{
		return $this->belongsTo(Oficina::class, 'id_oficina');
	}

	public function detalle_cupos()
	{
		return $this->hasMany(DetalleCupo::class, 'id_cupo');
	}
}
