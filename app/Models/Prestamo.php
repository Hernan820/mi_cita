<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Prestamo
 * 
 * @property int $id
 * @property string|null $num_prestamo
 * @property string|null $Direccion_propiedad
 * @property string|null $saldo_prestamo
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Collection|Solicitude[] $solicitudes
 *
 * @package App\Models
 */
class Prestamo extends Model
{
	protected $table = 'prestamos';

	protected $fillable = [
		'num_prestamo',
		'Direccion_propiedad',
		'saldo_prestamo'
	];

	public function solicitudes()
	{
		return $this->hasMany(Solicitude::class, 'id_prestamo');
	}
}
