<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Estado
 * 
 * @property int $id
 * @property string $nombre
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Collection|DetalleCupo[] $detalle_cupos
 *
 * @package App\Models
 */
class Estado extends Model
{
	protected $table = 'estados';

	protected $fillable = [
		'nombre'
	];

	public function detalle_cupos()
	{
		return $this->hasMany(DetalleCupo::class, 'id_estado');
	}
}
