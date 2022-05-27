<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class CuposHorario
 * 
 * @property int $id
 * @property int $id_cupo
 * @property int $id_horario
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class CuposHorario extends Model
{
	protected $table = 'cupos_horarios';

	protected $casts = [
		'id_cupo' => 'int',
		'id_horario' => 'int'
	];

	protected $fillable = [
		'id_cupo',
		'id_horario'
	];
}
