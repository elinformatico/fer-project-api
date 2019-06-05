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

    # Correspondencia
    Route::get('get/dependencias', 'Correspondencia@getDependencias');
    Route::post('store/dependencia', 'Correspondencia@registrarCorrespondencia');
	Route::get('get/memos-oficios/correspondencias/{correspondenciaId}', 'Correspondencia@getCorrespondencia');
	Route::post('store/memos-oficios', 'OficioYMemo@registrarMemoOficio');
    
    # Consultas
    Route::post('get/consultas/correspondencia', 'Consultas@getCorrespondencia');
    Route::post('get/consultas/memos-oficios', 'Consultas@getMemosOficios');
    
    # Export PDF
    Route::get('get/pdf', 'Consultas@generatePdf');
    
    # Example: https://s3-eu-west-1.amazonaws.com/htmlpdfapi.production/free_html5_invoice_templates/example2/index.html
    Route::get('get/html', function () {
        return view('example',  [
            'quantity'      => '1' ,
            'description'   => 'some ramdom text',
            'price'         => '500',
            'total'         => '500'
        ]);
    });
    
    # Export CSV
    Route::get('get/csv', 'Consultas@generateCSV');
    
    # Validate Token
    Route::get("validate-session/{token}", 'Utils@validateSession');
});