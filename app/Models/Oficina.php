<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Oficina
 * 
 * @property int $id
 * @property string $nombre
 * @property string|null $direccion
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Collection|Cupo[] $cupos
 *
 * @package App\Models
 */
class Oficina extends Model
{
	protected $table = 'oficinas';

	protected $fillable = [
		'nombre',
		'direccion'
	];

	public function cupos()
	{
		return $this->hasMany(Cupo::class, 'id_oficina');
	}
}
