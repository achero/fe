<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class DocInvoiceTaxTotalTaxSubtotalTaxCategoryTaxScheme extends Model {

	protected $table = 'doc_invoice_tax_total_tax_subtotal_tax_category_tax_scheme';
	protected $primaryKey = 'n_id_invoice_tax_total';

	public $incrementing = false;
	public $timestamps = false;

	public function docInvoiceTaxTotal()
	{
		return $this->belongsTo('DocInvoiceTaxTotal', $this->primaryKey);
	}

}