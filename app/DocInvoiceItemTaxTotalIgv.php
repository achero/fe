<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class DocInvoiceItemTaxTotalIgv extends Model {

	protected $table = 'doc_invoice_item_tax_total_igv';
	protected $primaryKey = 'n_id_invoice_item_tax_total';

	public $incrementing = false;
	public $timestamps = false;

	public function docInvoiceItemTaxTotal()
	{
		return $this->belongsTo('DocInvoiceItemTaxTotal', $this->primaryKey);
	}

}