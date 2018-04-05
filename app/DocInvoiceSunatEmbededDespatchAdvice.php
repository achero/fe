<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class DocInvoiceSunatEmbededDespatchAdvice extends Model
{

    protected $table = 'doc_invoice_sunat_embeded_despatch_advice';
    protected $primaryKey = 'n_id_invoice';
    public $timestamps = false;

    public function docInvoice()
    {
        return $this->belongsTo('DocInvoice', $this->primaryKey);
    }

    public function docInvoiceSunatEmbededDespatchAdviceOriginDelivery()
    {
        return $this->hasMany('DocInvoiceSunatEmbededDespatchAdviceOriginDelivery', $this->primaryKey);
    }

}
