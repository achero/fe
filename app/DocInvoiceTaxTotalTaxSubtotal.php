<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class DocInvoiceTaxTotalTaxSubtotal extends Model {

	protected $table = 'doc_invoice_tax_total_tax_subtotal';
	protected $primaryKey = 'n_id_invoice_tax_total';

	public $incrementing = false;
	public $timestamps = false;

	public function docInvoiceTaxTotal()
	{
		return $this->belongsTo('DocInvoiceTaxTotal', $this->primaryKey);
	}
}