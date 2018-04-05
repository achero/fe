<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class DocInvoicePdfDataCustom extends Model
{

    protected $table = 'doc_invoice_pdf_data_custom';
    protected $primaryKey = 'n_id_invoice_pdf_data_custom';
    public $incrementing = true;
    public $timestamps = false;

    public function docInvoicePdfData()
    {
        return $this->belongsTo('DocInvoicePdfData', 'n_id_invoice');
    }

}
