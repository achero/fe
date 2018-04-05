<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class DocInvoiceTypeCode extends Model
{

    protected $table = 'doc_invoice_type_code';
    protected $primaryKey = 'c_invoice_type_code';
    public $incrementing = false;
    public $timestamps = false;

    public function docInvoice()
    {
        return $this->hasMany('DocInvoice', $this->primaryKey);
    }

}
