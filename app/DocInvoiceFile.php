<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class DocInvoiceFile extends Model
{

	protected $table = 'doc_invoice_file';
	protected $primaryKey = 'n_id_invoice';
	public $incrementing = false;

	const CREATED_AT = 'd_date_register';
	const UPDATED_AT = 'd_date_update';

	public function docInvoice()
    {
        return $this->belongsTo('App\DocInvoice', $this->primaryKey);
    }

}
