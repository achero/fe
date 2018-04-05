<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class LogPhase extends Model
{

    protected $table = 'log_phase';
    protected $primaryKey = 'lgph_id';

    public function logLog()
    {
        return $this->hasMany('LogLog', $this->primaryKey);
    }

}
