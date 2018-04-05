<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class DocInvoiceAnticipos extends Model
{

    protected $table = 'doc_invoice_anticipos';
    protected $primaryKey = 'ant_id';
    public $timestamps = false;

    public function docInvoice()
    {
        return $this->belongsTo('DocInvoice', 'n_id_invoice');
    }

}
