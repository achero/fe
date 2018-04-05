<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class DocInvoiceTaxTotal extends Model
{

    protected $table = 'doc_invoice_tax_total';
    protected $primaryKey = 'n_id_invoice_tax_total';
    public $timestamps = false;

    public function docInvoiceTaxTotalTaxSubTotalTaxCategoryTaxScheme()
    {
        return $this->hasOne('App\DocInvoiceTaxTotalTaxSubtotalTaxCategoryTaxScheme', $this->primaryKey);
    }

    public function docInvoiceTaxTotalTaxSubtotal()
    {
    	return $this->hasOne('App\DocInvoiceTaxTotalTaxSubtotal', $this->primaryKey);
    }

    public function docInvoice()
    {
    	return $this->belongsTo('App\DocInvoice', 'n_id_invoice');
    }

}
