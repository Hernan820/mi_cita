<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ClienteSolicitude
 * 
 * @property int $id
 * @property int $id_cliente
 * @property int $id_solicitud
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Cliente $cliente
 * @property Solicitude $solicitude
 * @property Collection|Gestione[] $gestiones
 *
 * @package App\Models
 */
class ClienteSolicitude extends Model
{
	protected $table = 'cliente_solicitudes';

	protected $casts = [
		'id_cliente' => 'int',
		'id_solicitud' => 'int'
	];

	protected $fillable = [
		'id_cliente',
		'id_solicitud'
	];

	public function cliente()
	{
		return $this->belongsTo(Cliente::class, 'id_cliente');
	}

	public function solicitude()
	{
		return $this->belongsTo(Solicitude::class, 'id_solicitud');
	}

	public function gestiones()
	{
		return $this->hasMany(Gestione::class, 'id_solicitud');
	}
}
