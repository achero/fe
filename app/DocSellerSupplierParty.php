<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class DocSellerSupplierParty extends Model
{

    protected $table = 'doc_seller_supplier_party';
    protected $primaryKey = 'n_id_invoice';
    public $incrementing = false;
    public $timestamps = false;

    public function docInvoice()
    {
        return $this->belongsTo('DocInvoice', 'n_id_invoice');
    }

}
