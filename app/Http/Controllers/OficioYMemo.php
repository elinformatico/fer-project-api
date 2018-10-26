<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use DB;

class OficioYMemo extends Controller
{
    public function registrarMemoOficio($tipo) 
    {
    	try {

    		print_r($_REQUEST);
    
    	} catch(\Illuminate\Database\QueryException $e){            
            return Response()->json(array('status' => 'error', 'msg'=>'Hubo un Error al registrar los datos','error'=>$e));
        }
    }
}
