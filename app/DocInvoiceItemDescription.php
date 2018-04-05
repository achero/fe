<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class DocInvoiceItemDescription extends Model
{

    protected $table = 'doc_invoice_item_description';
    protected $primaryKey = 'n_id_invoice_item_description';
    public $timestamps = false;

    public function docInvoiceItem()
    {
        return $this->belongsTo('DocInvoiceItem', 'n_id_invoice_item');
    }

}
