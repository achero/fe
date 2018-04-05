<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class DocInvoiceItemTaxTotalTaxSubtotal extends Model {

	protected $table = 'doc_invoice_item_tax_total_tax_subtotal';
	protected $primaryKey = 'n_id_invoice_item_tax_total';

	public $incrementing = false;
	public $timestamps = false;

	public function docInvoiceItemTaxTotal()
	{
		return $this->belongsTo('DocInvoiceItemTaxTotal', $this->primaryKey);
	}

}