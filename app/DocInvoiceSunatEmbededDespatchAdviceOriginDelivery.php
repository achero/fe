<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class DocInvoiceSunatEmbededDespatchAdviceOriginDelivery extends Model
{

    protected $table = 'doc_invoice_sunat_embeded_despatch_advice_origin_delivery';
    protected $primaryKey = 'isedaod_id';
    public $timestamps = false;

    public function docInvoiceSunatEmbededDespatchAdvice()
    {
        return $this->belongsTo('DocInvoiceSunatEmbededDespatchAdvice', $this->primaryKey);
    }

}
