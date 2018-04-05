<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class LogLogLevel extends Model {

	protected $table = 'log_log_level';
	protected $primaryKey = 'c_id_log_level';

	public $incrementing = false;
	public $timestamps = false;

}