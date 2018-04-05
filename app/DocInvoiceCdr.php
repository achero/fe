<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class DocInvoiceCdr extends Model {

	protected $table = 'doc_invoice_cdr';
	protected $primaryKey = 'n_id_invoice';
	public $incrementing = false;

	const CREATED_AT = 'd_date_register';
	const UPDATED_AT = 'd_date_update';
	
	public function docInvoice()
	{
		return $this->belongsTo('App\DocInvoice', $this->primaryKey);
	}

	public function docInvoiceCdrSenderParty()
	{
		return $this->hasOne('App\DocInvoiceCdrSenderParty', $this->primaryKey);
	}

	public function docInvoiceCdrReceiverParty()
	{
		return $this->hasOne('App\DocInvoiceCdrReceiverParty', $this->primaryKey);
	}

	public function docInvoiceCdrNote()
	{
		return $this->hasMany('App\DocInvoiceCdrNote', $this->primaryKey);
	}

	public function docInvoiceCdrDocumentResponse()
	{
		return $this->hasOne('App\DocInvoiceCdrDocumentResponse', $this->primaryKey);
	}

}