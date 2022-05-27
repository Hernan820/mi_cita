<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class User
 * 
 * @property int $id
 * @property string $name
 * @property string $email
 * @property int|null $id_pais
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property bool|null $estado_user
 * @property int|null $site
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Paise|null $paise
 * @property Collection|DetalleCupo[] $detalle_cupos
 * @property Collection|Gestione[] $gestiones
 * @property Collection|Nota[] $notas
 * @property Collection|Solicitude[] $solicitudes
 * @property Collection|Vitacora[] $vitacoras
 *
 * @package App\Models
 */
class User extends Model
{
	protected $table = 'users';

	protected $casts = [
		'id_pais' => 'int',
		'estado_user' => 'bool',
		'site' => 'int'
	];

	protected $dates = [
		'email_verified_at'
	];

	protected $hidden = [
		'password',
		'remember_token'
	];

	protected $fillable = [
		'name',
		'email',
		'id_pais',
		'email_verified_at',
		'password',
		'estado_user',
		'site',
		'remember_token'
	];

	public function paise()
	{
		return $this->belongsTo(Paise::class, 'id_pais');
	}

	public function detalle_cupos()
	{
		return $this->hasMany(DetalleCupo::class, 'id_usuario');
	}

	public function gestiones()
	{
		return $this->hasMany(Gestione::class, 'id_usuario');
	}

	public function notas()
	{
		return $this->hasMany(Nota::class, 'id_usuario');
	}

	public function solicitudes()
	{
		return $this->hasMany(Solicitude::class, 'loan_officer');
	}

	public function vitacoras()
	{
		return $this->hasMany(Vitacora::class, 'id_usuario');
	}
}
