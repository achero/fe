<?php

class AccAccountUser extends Eloquent {

	protected $table = 'acc_account_user';
	protected $primaryKey = 'n_id_account';
	
	const CREATED_AT = 'd_date_register_account_user';
	const UPDATED_AT = 'd_date_update_account_user';

	public $incrementing = false;

}