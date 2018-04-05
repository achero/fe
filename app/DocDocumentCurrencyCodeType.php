<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class DocDocumentCurrencyCodeType extends Model {

	protected $table = 'doc_document_currency_code_type';
	protected $primaryKey = 'c_document_currency_code';

	public $incrementing = false;
	public $timestamps = false;
    
    public function docInvoice() {
		return $this->hasOne('DocInvoice', $this->primaryKey);
	}

}