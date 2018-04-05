<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class DocInvoiceBillingReferenceInvoiceDocumentReference extends Model {

	protected $table = 'doc_invoice_billing_reference_invoice_document_reference';
	protected $primaryKey = 'n_id_invoice';

	public $incrementing = false;
	public $timestamps = false;

}