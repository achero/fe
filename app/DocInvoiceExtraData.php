<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class DocInvoiceExtraData extends Model
{
	protected $table = 'doc_invoice_extra_data';
	protected $primaryKey = 'n_id_invoice';

	public $incrementing = false;
	public $timestamps = false;

	public function docInvoice() {
		return $this->belongsTo('DocInvoice', $this->primaryKey);
	}
}