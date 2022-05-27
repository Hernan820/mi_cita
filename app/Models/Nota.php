<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Nota
 * 
 * @property int $id
 * @property string $nota
 * @property Carbon $fecha
 * @property int $id_usuario
 * @property int $id_cita
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property DetalleCupo $detalle_cupo
 * @property User $user
 *
 * @package App\Models
 */
class Nota extends Model
{
	protected $table = 'notas';

	protected $casts = [
		'id_usuario' => 'int',
		'id_cita' => 'int'
	];

	protected $dates = [
		'fecha'
	];

	protected $fillable = [
		'nota',
		'fecha',
		'id_usuario',
		'id_cita'
	];

	public function detalle_cupo()
	{
		return $this->belongsTo(DetalleCupo::class, 'id_cita');
	}

	public function user()
	{
		return $this->belongsTo(User::class, 'id_usuario');
	}
}
