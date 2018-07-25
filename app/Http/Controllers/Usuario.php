<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use DB;

class Usuario extends Controller
{
    public function getUsuarios() 
    {
    	try {
            $usuarios = DB::table('usuario')->get();
	    	return Response()->json(
	    		array('msg'=>'Vientos, todo jala chido', 'usuarios' => $usuarios)
	    	);
        } catch(\Illuminate\Database\QueryException $e){
            return Response()->json(
            	array('msg'=>'Error de query al consultar los datos.','error'=>$e)
            );
        }
    }
}
