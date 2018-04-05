<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class DocInvoiceCdrStatus extends Model {

	protected $table = 'doc_invoice_cdr_status';
	protected $primaryKey = 'n_id_invoice';
	public $incrementing = false;

	const CREATED_AT = 'd_date_register';
	const UPDATED_AT = 'd_date_update';
	
	public function docInvoice()
	{
		return $this->belongsTo('App\DocInvoice', $this->primaryKey);
	}

	public function docCdrStatus()
	{
		return $this->belongsTo('App\DocCdrStatus', 'n_id_cdr_status');
	}

}