<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Procesadore
 * 
 * @property int $id
 * @property string|null $nombre
 * @property string|null $apellidos
 * @property string|null $correo
 * @property int|null $estado_procesador
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Collection|Solicitude[] $solicitudes
 *
 * @package App\Models
 */
class Procesadore extends Model
{
	protected $table = 'procesadores';

	protected $casts = [
		'estado_procesador' => 'int'
	];

	protected $fillable = [
		'nombre',
		'apellidos',
		'correo',
		'estado_procesador'
	];

	public function solicitudes()
	{
		return $this->hasMany(Solicitude::class, 'id_procesador');
	}
}
