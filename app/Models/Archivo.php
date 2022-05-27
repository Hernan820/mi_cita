<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Archivo
 * 
 * @property int $id
 * @property string $ruta
 * @property string|null $nombre_archivo
 * @property int $id_solicitud
 * @property int|null $estado_archivo
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Solicitude $solicitude
 *
 * @package App\Models
 */
class Archivo extends Model
{
	protected $table = 'archivos';

	protected $casts = [
		'id_solicitud' => 'int',
		'estado_archivo' => 'int'
	];

	protected $fillable = [
		'ruta',
		'nombre_archivo',
		'id_solicitud',
		'estado_archivo'
	];

	public function solicitude()
	{
		return $this->belongsTo(Solicitude::class, 'id_solicitud');
	}
}
