<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class DocInvoiceItemPricingReferenceAlternativeConditionPrice extends Model {

	protected $table = 'doc_invoice_item_pricing_reference_alternative_condition_price';
	protected $primaryKey = 'n_id_invoice_item_pricing_reference_alternative_condition_price';
	
	public $timestamps = false;

	public function docInvoiceItem()
	{
		return $this->belongsTo('DocInvoiceItem', 'n_id_invoice_item');
	}

}