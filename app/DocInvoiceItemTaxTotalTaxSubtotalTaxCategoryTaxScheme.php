<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class DocInvoiceItemTaxTotalTaxSubtotalTaxCategoryTaxScheme extends Model {

	protected $table = 'doc_invoice_item_tax_total_tax_subtotal_tax_category_tax_scheme';
	protected $primaryKey = 'n_id_invoice_item_tax_total';

	public $incrementing = false;
	public $timestamps = false;

	public function docInvoiceItemTaxTotal()
	{
		return $this->belongsTo('DocInvoiceItemTaxTotal', $this->primaryKey);
	}

}