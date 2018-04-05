<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class DocInvoiceLegalMonetaryTotal extends Model {

	protected $table = 'doc_invoice_legal_monetary_total';
	protected $primaryKey = 'n_id_invoice';

	public $incrementing = false;
	public $timestamps = false;

}