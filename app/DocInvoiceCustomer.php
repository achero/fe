<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class DocInvoiceCustomer extends Model
{

    protected $table = 'doc_invoice_customer';
    protected $primaryKey = 'n_id_invoice';
    public $incrementing = false;
    public $timestamps = false;

    public function docInvoice()
    {
        return $this->belongsTo('DocInvoice', $this->primaryKey);
    }

}
