<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

# Home
Route::get('/', function(){

	return "welcome to factura";

});

# Home
Route::get('dashboard', 'HomeController@index');


# Procesamiento individual de documentos
Route::post('process-txt', 'Admin\\ProcessInputTxtController@upload');


# Consulta de Tickets para resumenes
Route::get('get-status-ticket/{ticket}', function($ticket){

	return App\Http\Controllers\Traits\SunatHelper::getStatus($ticket);

});


# phpInfo
Route::get('/test-php', function(){

	echo phpinfo();

});


# Test PDF
Route::get('/test-pdf', function(){


	//Field: doc_invoice.n_id_invoice
	$id = 423;
	return App\Http\Controllers\Traits\UtilHelper::pdf($id);


});