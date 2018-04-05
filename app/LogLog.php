<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class LogLog extends Model
{

    protected $table = 'log_log';
    protected $primaryKey = 'n_id_log';

    const CREATED_AT = 'd_date_register';
    const UPDATED_AT = 'd_date_update';

    public function logLogLevel()
    {
        return $this->belongsTo('LogLogLevel', 'c_id_log_level');
    }

    public function docInvoice()
    {
        return $this->belongsTo('DocInvoice', 'n_id_invoice');
    }

    public function logPhase()
    {
        return $this->belongsTo('LogPhase', 'lgph_id');
    }

    public function docInvoiceTypeCode()
    {
        return $this->belongsTo('DocInvoiceTypeCode', 'c_invoice_type_code');
    }

}
