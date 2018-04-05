<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class DocInvoiceItemBillingPayment extends Model
{

    protected $table = 'doc_invoice_item_billing_payment';
    protected $primaryKey = 'n_id_invoice_item_billing_payment';
    public $timestamps = false;

    public function docInvoiceItem()
    {
        return $this->belongsTo('DocInvoiceItem', 'n_id_invoice_item', 'n_id_invoice_item');
    }

}
