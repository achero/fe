<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class SupSupplier extends Model
{

    protected $table = 'sup_supplier';
    protected $primaryKey = 'n_id_supplier';

    const CREATED_AT = 'd_date_register_supplier';
    const UPDATED_AT = 'd_date_update_supplier';

    public function docInvoice()
    {
        return $this->hasMany('App\DocInvoice', $this->primaryKey);
    }

    public function supSupplierFtp()
    {
        return $this->hasOne('App\SupSupplierFtp', $this->primaryKey);
    }

    public function supSupplierConfiguration()
    {
        return $this->hasOne('App\SupSupplierConfiguration', $this->primaryKey);
    }

    public function supSupplierSignature()
    {
        return $this->hasOne('App\SupSupplierSignature', $this->primaryKey);
    }

    public function AccAccount()
    {
        return $this->hasMany('App\AccAccount', $this->primaryKey);
    }

    public function country()
    {
        return $this->belongsTo('App\Country', 'c_party_postal_address_country_identification_code', 'c_iso');
    }

    public function errError()
    {
        return $this->hasMany('App\ErrError', $this->primaryKey);
    }

    public function queQueue()
    {
        return $this->hasMany('App\QueQueue', $this->primaryKey);
    }

}
