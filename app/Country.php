<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class Country extends Model {

	protected $table = 'country';
	protected $primaryKey = 'c_iso';
	
	public $incrementing = false;
	public $timestamps = false;
    
    public function supSupplier()
    {
        return $this->hasMany('SupSupplier', 'c_party_postal_address_country_identification_code', $this->primaryKey);
    }
    
    public function docInvoiceSupplier()
    {
        return $this->hasMany('DocInvoiceSupplier', 'c_party_postal_address_country_identification_code', $this->primaryKey);
    }

}