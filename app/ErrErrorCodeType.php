<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class ErrErrorCodeType extends Model {

	protected $table = 'err_error_code_type';
	protected $primaryKey = 'n_id_error_code_type';
	
	public $timestamps = false;

	public function errErrorCode()
	{
		return $this->hasMany('ErrErrorCodeType', $this->primaryKey);
	}

}