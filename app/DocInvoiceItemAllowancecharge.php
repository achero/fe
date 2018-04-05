<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class DocInvoiceItemAllowancecharge extends Model {

	protected $table = 'doc_invoice_item_allowancecharge';
	protected $primaryKey = 'n_id_invoice_item';
	
	public $timestamps = false;
	public $incrementing = false;

}