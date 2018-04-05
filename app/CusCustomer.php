<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class CusCustomer extends Model {

	protected $table = 'cus_customer';
	protected $primaryKey = 'n_id_customer';
	
	const CREATED_AT = 'd_date_register_customer';
	const UPDATED_AT = 'd_date_update_customer';

}