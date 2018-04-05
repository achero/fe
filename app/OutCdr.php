<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class OutCdr extends Model
{

    protected $table = 'out_cdr';
    protected $primaryKey = 'cdr_id';
    public $incrementing = true;

    const CREATED_AT = 'cdr_created_at';
    const UPDATED_AT = 'cdr_updated_at';

    public function outCdrDet()
    {
        return $this->hasMany('OutCdrDet', $this->primaryKey);
    }

}
