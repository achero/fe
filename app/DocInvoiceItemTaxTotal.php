<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class DocInvoiceItemTaxTotal extends Model {

	protected $table = 'doc_invoice_item_tax_total';
	protected $primaryKey = 'n_id_invoice_item_tax_total';
	
	public $timestamps = false;

	public function docInvoiceItemTaxTotalIgv() {
		return $this->hasOne('App\DocInvoiceItemTaxTotalIgv', $this->primaryKey);
	}

	public function docInvoiceItemTaxTotalTaxSubtotal() {
		return $this->hasOne('App\DocInvoiceItemTaxTotalTaxSubtotal', $this->primaryKey);
	}

	public function docInvoiceItemTaxTotalIsc() {
		return $this->hasOne('App\DocInvoiceItemTaxTotalIsc', $this->primaryKey);
	}

	public function docInvoiceItemTaxTotalTaxSubtotalTaxCategoryTaxScheme() {
		return $this->hasOne('App\DocInvoiceItemTaxTotalTaxSubtotalTaxCategoryTaxScheme', $this->primaryKey);
	}

	public function docInvoiceItem()
	{
		return $this->belongsTo('App\DocInvoiceItem', 'n_id_invoice_item', 'n_id_invoice_item');
	}

}