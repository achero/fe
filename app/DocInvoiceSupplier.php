<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class DocInvoiceSupplier extends Model
{

    protected $table = 'doc_invoice_supplier';
    protected $primaryKey = 'n_id_invoice';
    public $incrementing = false;
    public $timestamps = false;

    public function country()
    {
        return $this->belongsTo('App\Country', 'c_party_postal_address_country_identification_code', 'c_iso');
    }
    
    public function supSupplier()
    {
        return $this->belongsTo('App\SupSupplier', 'n_id_supplier');
    }

}
