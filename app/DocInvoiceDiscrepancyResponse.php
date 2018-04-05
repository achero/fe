<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class DocInvoiceDiscrepancyResponse extends Model {

	protected $table = 'doc_invoice_discrepancy_response';
	protected $primaryKey = 'n_id_invoice';

	public $incrementing = false;
	public $timestamps = false;

}