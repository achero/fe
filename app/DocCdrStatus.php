<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class DocCdrStatus extends Model {

	protected $table = 'doc_cdr_status';
	protected $primaryKey = 'n_id_cdr_status';
	public $incrementing = false;

	const CREATED_AT = 'd_date_register';
	const UPDATED_AT = 'd_date_update';
	
	public function docInvoiceCdrStatus()
	{
		return $this->hasMany('DocInvoiceCdrStatus', $this->primaryKey);
	}

}