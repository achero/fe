<?php

use Illuminate\Auth\UserTrait;
use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableTrait;
use Illuminate\Auth\Reminders\RemindableInterface;

class AccAccount extends Eloquent implements UserInterface, RemindableInterface {

	use UserTrait, RemindableTrait;

	const CREATED_AT = 'd_date_register_account';
	const UPDATED_AT = 'd_date_update_account';

	protected $primaryKey = 'n_id_account';

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'acc_account';

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = array('password', 'remember_token');

	public function accAccountUser() {
		return $this->hasOne('AccAccountUser', $this->primaryKey);
	}
    
    public function logAccount() {
		return $this->hasOne('LogAccount', $this->primaryKey);
	}

	public function supSupplier() {
		return $this->belongsTo('SupSupplier', 'n_id_supplier');
	}

}
