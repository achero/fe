<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class DocInvoiceCdrDocumentResponse extends Model
{

    protected $table = 'doc_invoice_cdr_document_response';
    protected $primaryKey = 'n_id_invoice';
    public $incrementing = false;
    public $timestamps = false;

    public function docInvoiceCdr()
    {
        return $this->belongsTo('App\DocInvoiceCdr', $this->primaryKey);
    }

    public function errErrorCode()
    {
        return $this->belongsTo('App\ErrErrorCode', 'c_response_response_code', 'c_id_error_code');
    }

}
