<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class DocInvoiceItem extends Model
{

	protected $table = 'doc_invoice_item';
	protected $primaryKey = 'n_id_invoice_item';
	public $timestamps = false;

	public function docInvoiceItemTaxTotal()
	{
		return $this->hasMany('App\DocInvoiceItemTaxTotal', $this->primaryKey);
	}
    
    public function docInvoiceItemDescription()
	{
		return $this->hasMany('App\DocInvoiceItemDescription', $this->primaryKey);
	}
	
	public function docInvoiceItemBillingPayment()
	{
		return $this->hasMany('App\DocInvoiceItemBillingPayment', $this->primaryKey);
	}

	public function docInvoiceItemAllowancecharge()
	{
		return $this->hasOne('App\DocInvoiceItemAllowancecharge', $this->primaryKey);
	}

	public function docInvoiceItemPricingReferenceAlternativeConditionPrice()
	{
		return $this->hasMany('App\DocInvoiceItemPricingReferenceAlternativeConditionPrice', $this->primaryKey);
	}

	public function docInvoice()
	{
		return $this->belongsTo('App\DocInvoice', 'n_id_invoice');
	}

}
