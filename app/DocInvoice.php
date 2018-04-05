<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class DocInvoice extends Model
{

    protected $table = 'doc_invoice';
    protected $primaryKey = 'n_id_invoice';

    const CREATED_AT = 'd_date_register_invoice';
    const UPDATED_AT = 'd_date_update_invoice';

    public function docInvoiceCustomer()
    {
        return $this->hasOne('App\DocInvoiceCustomer', $this->primaryKey);
    }

    public function docInvoiceSunatEmbededDespatchAdvice()
    {
        return $this->hasOne('App\DocInvoiceSunatEmbededDespatchAdvice', $this->primaryKey);
    }

    public function logInvoice()
    {
        return $this->hasMany('App\LogInvoice', $this->primaryKey);
    }

    public function docInvoiceDespatchDocumentReference()
    {
        return $this->hasMany('App\DocInvoiceDespatchDocumentReference', $this->primaryKey);
    }

    public function docInvoiceAdditionalInformationAdditionalProperty()
    {
        return $this->hasMany('App\DocInvoiceAdditionalInformationAdditionalProperty', $this->primaryKey);
    }

    public function docInvoiceSupplier()
    {
        return $this->hasOne('App\DocInvoiceSupplier', $this->primaryKey);
    }

    public function docInvoiceLegalMonetaryTotal()
    {
        return $this->hasOne('App\DocInvoiceLegalMonetaryTotal', $this->primaryKey);
    }

    public function docInvoiceAdditionalInformationAdditionalMonetaryTotal()
    {
        return $this->hasMany('App\DocInvoiceAdditionalInformationAdditionalMonetaryTotal', $this->primaryKey);
    }

    public function docInvoiceAdditionalDocumentReference()
    {
        return $this->hasMany('App\DocInvoiceAdditionalDocumentReference', $this->primaryKey);
    }

    public function docInvoiceItem()
    {
        return $this->hasMany('App\DocInvoiceItem', $this->primaryKey);
    }

    public function docInvoiceDiscrepancyResponse()
    {
        return $this->hasOne('App\DocInvoiceDiscrepancyResponse', $this->primaryKey);
    }

    public function docInvoiceFile()
    {
        return $this->hasOne('App\DocInvoiceFile', $this->primaryKey);
    }

    public function docInvoiceTaxTotal()
    {
        return $this->hasMany('App\DocInvoiceTaxTotal', $this->primaryKey);
    }

    public function docInvoiceBillingReferenceInvoiceDocumentReference()
    {
        return $this->hasOne('App\DocInvoiceBillingReferenceInvoiceDocumentReference', $this->primaryKey);
    }

    public function docInvoiceItemPricingReferenceAlternativeConditionPrice()
    {
        return $this->hasManyThrough('App\DocInvoiceItemPricingReferenceAlternativeConditionPrice', 'DocInvoiceItem',
                'n_id_invoice', 'n_id_invoice_item');
    }

    public function docInvoiceItemTaxTotal()
    {
        return $this->hasManyThrough('App\DocInvoiceItemTaxTotal', 'DocInvoiceItem', 'n_id_invoice', 'n_id_invoice_item');
    }

    public function docDocumentCurrencyCodeType()
    {
        return $this->belongsTo('App\DocDocumentCurrencyCodeType', 'c_document_currency_code', 'c_document_currency_code');
    }

    public function supSupplier()
    {
        return $this->belongsTo('App\SupSupplier', 'n_id_supplier');
    }

    public function docInvoiceTypeCode()
    {
        return $this->belongsTo('App\DocInvoiceTypeCode', 'c_invoice_type_code');
    }

    public function queFile()
    {
        return $this->belongsTo('App\QueFile', 'n_id_queue_file');
    }

    public function docInvoiceCdrStatus()
    {
        return $this->hasOne('App\DocInvoiceCdrStatus', $this->primaryKey);
    }

    public function docInvoicePdfData()
    {
        return $this->hasOne('App\DocInvoicePdfData', $this->primaryKey);
    }

    public function docInvoiceExtraData()
    {
        return $this->hasOne('App\DocInvoiceExtraData', $this->primaryKey);
    }

    public function docInvoiceCdr()
    {
        return $this->hasOne('App\DocInvoiceCdr', $this->primaryKey);
    }

    public function docInvoiceTicket()
    {
        return $this->hasOne('App\DocInvoiceTicket', $this->primaryKey);
    }

    public function docInvoiceRelated()
    {
        return $this->hasMany('App\DocInvoice', 'n_id_invoice_related', 'n_id_invoice');
    }

    public function docInvoiceBelongs()
    {
        return $this->belongsTo('App\DocInvoice', 'n_id_invoice', 'n_id_invoice_related');
    }

    public function docInvoiceInput()
    {
        return $this->hasOne('App\DocInvoiceInput', $this->primaryKey);
    }

    public function errError()
    {
        return $this->hasMany('App\ErrError', $this->primaryKey);
    }

    public function docInvoiceSignature()
    {
        return $this->hasOne('App\DocInvoiceSignature', $this->primaryKey);
    }

    public function DocInvoiceAnticipos()
    {
        return $this->hasMany('App\DocInvoiceAnticipos', $this->primaryKey);
    }

    public function docSellerSupplierParty()
    {
        return $this->hasOne('App\DocSellerSupplierParty', $this->primaryKey);
    }

}
