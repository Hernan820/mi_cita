<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Paise
 * 
 * @property int $id
 * @property string $nombre_paises
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Collection|User[] $users
 *
 * @package App\Models
 */
class Paise extends Model
{
	protected $table = 'paises';

	protected $fillable = [
		'nombre_paises'
	];

	public function users()
	{
		return $this->hasMany(User::class, 'id_pais');
	}
}
