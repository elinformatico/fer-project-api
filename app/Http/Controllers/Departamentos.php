<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use DB;

class Departamentos extends Controller
{
    public function getDepartamentos() 
    {
        try {
            $departamentos = DB::table('departamento')
            			->select(
            				"dep_id as id",
							"dep_nombre as nombre",
							"dep_fecha_creacion as fechaCreacion"
            			)->get();

            if(count($departamentos) > 0) {
	            return Response()->json(
	                array(
	                    'msg'			=> 'Departamentos obtenidos satisfactoriamente', 
	                    'departamentos' => $departamentos,
	                    'status'		=> "success",
	                )
	            );
            } else {
            	return Response()->json(
	                array(
	                    'msg'			=> 'No se encontraron Departamentos registrados', 
	                    'departamentos' => [],
	                    'status'		=> "error",
	                )
	            );
            }

        } catch(\Illuminate\Database\QueryException $e){
            return Response()->json(
                array('msg'=>'Error de query al consultar los datos.','error'=>$e)
            );
        }
    }
}
