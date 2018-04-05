<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class DocInvoicePdfData extends Model
{

    protected $table = 'doc_invoice_pdf_data';
    protected $primaryKey = 'n_id_invoice';
    public $incrementing = false;
    public $timestamps = false;

    public function docInvoice()
    {
        return $this->belongsTo('App\DocInvoice', $this->primaryKey);
    }

    public function docInvoicePdfDataCustom()
    {
        return $this->hasMany('App\DocInvoicePdfDataCustom', $this->primaryKey);
    }

}
