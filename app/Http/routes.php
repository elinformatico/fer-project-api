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
    # ------------------------------------------------------------------------
	Route::get('get/usuarios', 'Usuario@getUsuarios');
	Route::get('get/usuario/{username}/{password}', 'Usuario@getUsuario');
	Route::get('get/nombreUsuarios', 'Usuario@getNombresUsuarios');
    Route::post('store/usuario', 'Usuario@registrarUsuario');
    
    # Validate Token
    Route::get("validate-session/{token}", 'Utils@validateSession');
    
    # Fernando's System End-Points
    # ------------------------------------------------------------------------
	Route::get('get/departamentos/', 'Departamentos@getDepartamentos');

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
});

# End-points for Finance System (Control Fuel)
Route::group(['prefix' => 'fnz/', 'middleware' => ['guest']], function() {
    
    # End-point to generate a Token
    Route::get('generate-token/{username}/{passPlainText}', 'Utils@generateDevToken');
    
    # Catalogs
    # ---------------------------------------------------------------------------------------
    Route::get('get/categories', 'Catalogs@getCategories');
    Route::get('get/paymentmethods', 'Catalogs@getPaymentMethods');
    Route::get('get/paymentmethods/{type}', 'Catalogs@getPaymentMethodsByType');
    Route::get('get/banks', 'Catalogs@getBanks');
    Route::get('get/cars', 'Catalogs@getCars');
    
    # Module "Fue Control" (Registro Gasolina)
    # ---------------------------------------------------------------------------------------
    Route::get('get/kilometraje/{carId}', 'GasolinaController@getUltimoKilometrajeByCar');
    Route::post('registrar/gasolina', 'GasolinaController@registrarGasolina');
    Route::get('get/gasto-mensual/{carId}', 'GasolinaController@getTotalCurrentMonthAmount');
    
    
    # Save the Financial Log
    # ---------------------------------------------------------------------------------------
    Route::post('store/financial/log', 'FinancialLog@saveFinancialLog');
    
    # Utils
    Route::get('register-login/{jsonData}', 'Utils@registerLogin');
});