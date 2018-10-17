<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::group(['prefix' => '/', 'middleware' => ['guest']], function() { 
      
    # Usuarios
	Route::get('get/usuarios', 'Usuario@getUsuarios');
	Route::get('get/usuario/{username}/{password}', 'Usuario@getUsuario');
	Route::get('get/nombreUsuarios', 'Usuario@getNombresUsuarios');
	
	# Departamentos
	Route::get('get/departamentos/', 'Departamentos@getDepartamentos');

	# Usuarios
    Route::post('store/usuario', 'Usuario@registrarUsuario');

});