<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class DocInvoiceCdrNote extends Model {

	protected $table = 'doc_invoice_cdr_note';
	protected $primaryKey = 'n_id_invoice_cdr_note';
	public $incrementing = false;
	public $timestamps = false;
	
	public function docInvoiceCdr()
	{
		return $this->belongsTo('DocInvoiceCdr', 'n_id_invoice');
	}

}