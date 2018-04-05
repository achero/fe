<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class SupSupplierFtp extends Model
{

    protected $table = 'sup_supplier_ftp';
    protected $primaryKey = 'n_id_supplier';
    public $incrementing = false;

    const CREATED_AT = 'd_date_register_supplier_ftp';
    const UPDATED_AT = 'd_date_update_supplier_ftp';

    public function queQueue()
    {
        return $this->hasMany('QueQueue', $this->primaryKey);
    }

    public function supSupplier()
    {
        return $this->belongsTo('SupSupplier', $this->primaryKey);
    }

}
