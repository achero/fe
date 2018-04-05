<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class DocInvoiceCdrReceiverParty extends Model {

	protected $table = 'doc_invoice_cdr_receiver_party';
	protected $primaryKey = 'n_id_invoice';
	public $incrementing = false;
	public $timestamps = false;
	
	public function docInvoiceCdr()
	{
		return $this->belongsTo('DocInvoiceCdr', $this->primaryKey);
	}

}