<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class OutCdrDet extends Eloquent
{

    protected $table = 'out_cdr_det';
    protected $primaryKey = 'cdrd_id';
    public $incrementing = true;
    public $timestamps = false;

    public function outCdr()
    {
        return $this->belongsTo('OutCdr', 'cdr_id');
    }

    public function docCdrStatus()
    {
        return $this->belongsTo('DocCdrStatus', 'n_id_cdr_status');
    }

    public function docInvoiceTypeCode()
    {
        return $this->belongsTo('DocInvoiceTypeCode', 'c_invoice_type_code');
    }

    public function docInvoice()
    {
        return $this->belongsTo('DocInvoice', 'n_id_invoice');
    }

    public function accAccount()
    {
        return $this->belongsTo('AccAccount', 'n_id_account');
    }

}
