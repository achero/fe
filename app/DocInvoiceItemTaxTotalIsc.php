<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class DocInvoiceItemTaxTotalIsc extends Model {

	protected $table = 'doc_invoice_item_tax_total_isc';
	protected $primaryKey = 'n_id_invoice_item_tax_total';

	public $incrementing = false;
	public $timestamps = false;

	public function docInvoiceItemTaxTotal()
	{
		return $this->belongsTo('DocInvoiceItemTaxTotal', $this->primaryKey);
	}
}