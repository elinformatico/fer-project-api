<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use DB;

class Correspondencia extends Controller
{
    public function getDependencias() 
    {
    	try {
            $dependencias = DB::table('dependencia')
            			->select(
            				"dpc_id as id",
							"dpc_nombre as nombre",
							"dpc_fecha_creacion as fechaCreacion"
            			)->get();

            if(count($dependencias) > 0) {
	            return Response()->json(
	                array(
	                    'msg'			=> 'dependencias obtenidas satisfactoriamente', 
	                    'dependencias' => $dependencias,
	                    'status'		=> "success",
	                )
	            );
            } else {
            	return Response()->json(
	                array(
	                    'msg'			=> 'No se encontraron dependencias registrados', 
	                    'dependencias' => [],
	                    'status'		=> "error",
	                )
	            );
            }

        } catch(\Illuminate\Database\QueryException $e){
            return Response()->json(
                array('msg'=>'Error de query al obtener los datos de dependencias.','error'=>$e)
            );
        }
    }

    public function registrarDependencia() 
    {
    	try {
            
            $fechaObtenida = explode("/", $_REQUEST['corrTiempoLimiteRespuesta']);
            $fechaLimiteRespuesta =  "{$fechaObtenida[2]}-{$fechaObtenida[0]}-$fechaObtenida[1] 23:59:59";
            
            $depenciaId = "";
            $departamentoId = "";

            # Verificamos si se va registrar o seleccionar nueva dependencia
            if(isset($_REQUEST['nuevaDependencia']) && $_REQUEST['nuevaDependencia'] === 'true')
            {
                $depenciaId = DB::table('dependencia')->insertGetId(
                    [
                        'dpc_nombre'           => $_REQUEST['txtNuevaDependencia'], 
                        'dpc_fecha_creacion'    => DB::raw('NOW()'),
                    ]
                );

                if($depenciaId == "") {
                    return Response()->json([
                        'msg'    => 'Hubo un error al intentar registar la dependencia', 
                        'status' => "error",
                    ]);
                }

            } else {
                $depenciaId = $_REQUEST['corrSelectedDependencia'];
            }

            # Verificamos si vamos a registrar departamento o no
            if(isset($_REQUEST['nuevoDepartamento']) && $_REQUEST['nuevoDepartamento'] === 'true')
            {
                $departamentoId = DB::table('departamento')->insertGetId(
                    [
                        'dep_nombre'           => $_REQUEST['txtNuevoDepartamento'], 
                        'dep_fecha_creacion'    => DB::raw('NOW()'),
                    ]
                );   

                if($departamentoId == "") {
                    return Response()->json([
                        'msg'    => 'Hubo un error al intentar registar el departamento', 
                        'status' => "error",
                    ]);
                }

            } else {
                $departamentoId = $_REQUEST['corrSelectedDepartamento'];
            }
            
            if($depenciaId != ""){

                $correspondenciaId = DB::table('correspondencia')->insertGetId(
                    [
                        'cor_referencia'       => $_REQUEST['corrReferencia'], 
                        'cor_dpc_id_fk'        => $depenciaId,
                        'cor_dep_id_fk'        => $departamentoId,
                        'cor_usr_id_fk'        => $_REQUEST['corrSelectedDirigidoA'],
                        'cor_descripcion'      => $_REQUEST['corrDescripcion'],
                        'cor_observaciones'    => $_REQUEST['corrObservaciones'],
                        'cor_reg_nueva_dpc'    => (($_REQUEST['nuevaDependencia'] === 'true') ? "1" : "0"),
                        'cor_limite_respuesta' => $fechaLimiteRespuesta,
                        'cor_fecha_creacion'   => DB::raw('NOW()'),
                    ]
                );

                return Response()->json([
                   'msg'    => 'La Correspondencia se registro Satisfactoriamente', 
                   'status' => "success",
                ]);

            } else {
                return Response()->json([
                   'msg'    => 'No se pudo registrar la dependencia correctamente', 
                   'status' => "error",
                ]);
            }

        } catch(\Illuminate\Database\QueryException $e){            
            return Response()->json(array('status' => 'error', 'msg'=>'Hubo un Error al registrar los datos','error'=>$e));
        }

    }
}
