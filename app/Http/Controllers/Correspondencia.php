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

    		print_r($_REQUEST);
            $depenciaId = "";

            // Verificamos si se va registrar o seleccionar nueva dependencia
            if(isset($_REQUEST['nuevaDependencia']) && $_REQUEST['nuevaDependencia'] === 'true'){
                echo "Registrando nueva dependencia";

                $depenciaId = DB::table('dependencia')->insertGetId(
                    [
                        'dpc_nombre'           => $_REQUEST['txtNuevaDependencia'], 
                        'dpc_fecha_creacion'    => DB::raw('NOW()'),
                    ]
                );

            } else {
                $depenciaId = $_REQUEST['corrSelectedDependencia'];

                echo "Registrando dependencia existente selecionada como {$_REQUEST['corrSelectedDependencia']}";
            }

            if($depenciaId != ""){

                $correspondenciaId = DB::table('correspondencia')->insertGetId(
                    [
                        'cor_referencia'       => $_REQUEST['corrReferencia'], 
                        'cor_dpc_id_fk'        => $depenciaId,
                        'cor_dep_id_fk'        => $_REQUEST['corrSelectedDepartamento'],
                        'cor_usr_id_fk'        => $_REQUEST['corrSelectedDirigidoA'],
                        'cor_descripcion'      => $_REQUEST['corrDescripcion'],
                        'cor_observaciones'    => $_REQUEST['corrObservaciones'],
                        'cor_reg_nueva_dpc'    => (($_REQUEST['nuevaDependencia'] === 'true') ? "1" : "0"),
                        'cor_limite_respuesta' => DB::raw('NOW()'),  # TODO, Get Real Date
                        'cor_fecha_creacion'   => DB::raw('NOW()'),
                    ]
                );

                echo "Se registro la correspondencia con el ID {$correspondenciaId}";

            } else {
                echo "No se pudo registrar la dependencia";
            }

        } catch(\Illuminate\Database\QueryException $e){            
            return Response()->json(array('status' => 'error', 'msg'=>'Hubo un Error al registrar los datos','error'=>$e));
        }

    }
}
