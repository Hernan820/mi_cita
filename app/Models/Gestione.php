<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Gestione
 * 
 * @property int $id
 * @property string $gestion
 * @property Carbon $fecha
 * @property int $id_usuario
 * @property int $id_solicitud
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property ClienteSolicitude $cliente_solicitude
 * @property User $user
 *
 * @package App\Models
 */
class Gestione extends Model
{
	protected $table = 'gestiones';

	protected $casts = [
		'id_usuario' => 'int',
		'id_solicitud' => 'int'
	];

	protected $dates = [
		'fecha'
	];

	protected $fillable = [
		'gestion',
		'fecha',
		'id_usuario',
		'id_solicitud'
	];

	public function cliente_solicitude()
	{
		return $this->belongsTo(ClienteSolicitude::class, 'id_solicitud');
	}

	public function user()
	{
		return $this->belongsTo(User::class, 'id_usuario');
	}
}
