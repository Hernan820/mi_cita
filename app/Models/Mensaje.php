<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Mensaje
 * 
 * @property int $id
 * @property string $texto_mensaje
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class Mensaje extends Model
{
	protected $table = 'mensajes';

	protected $fillable = [
		'texto_mensaje'
	];
}
