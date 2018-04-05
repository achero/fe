<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class ErrErrorCode extends Model {

	protected $table = 'err_error_code';
	protected $primaryKey = 'c_id_error_code';
	
	public $incrementing = false;
	public $timestamps = false;

	public function errErrorCodeType()
	{
		return $this->belongsTo('App\ErrErrorCodeType', 'n_id_error_code_type');
	}

	public function errErrorCode()
	{
		return $this->hasMany('App\DocInvoiceCdrDocumentResponse', 'c_response_response_code', 'c_id_error_code');
	}

}