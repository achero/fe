<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class SupSupplierConfiguration extends Model
{

    protected $table = 'sup_supplier_configuration';
    protected $primaryKey = 'n_id_supplier';
    public $incrementing = false;
    public $timestamps = false;

    public function supSupplierConfigurationPaperSize()
    {
        return $this->belongsTo('App\SupSupplierConfigurationPaperSize', 'n_id_paper_size');
    }

    public function supSupplier()
    {
        return $this->belongsTo('App\SupSupplier', $this->primaryKey);
    }

}
