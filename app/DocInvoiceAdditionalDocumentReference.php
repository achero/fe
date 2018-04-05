<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class DocInvoiceAdditionalDocumentReference extends Model {

	protected $table = 'doc_invoice_additional_document_reference';
	protected $primaryKey = 'n_id_invoice_additional_document_reference';

	public $incrementing = false;
	public $timestamps = false;

	public function docInvoice()
	{
		return $this->belongsTo('DocInvoice', 'n_id_invoice');
	}

}