<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use DB;

class OficioYMemo extends Controller
{
    public function registrarMemoOficio() 
    {
    	try {
            
            $turnadoA_id_fk = "0";
            $turnadoA_id_txt = "";

            if(isset($_REQUEST['tipoTurnadoA']) && $_REQUEST['tipoTurnadoA'] == "dependencia"){
                $turnadoA_id_fk = $_REQUEST['turnadoA_dep_correspondencia'];

            } else if(isset($_REQUEST['tipoTurnadoA']) && $_REQUEST['tipoTurnadoA'] == "usuario"){
                $turnadoA_id_fk = $_REQUEST['turnadoA_usuario'];

            } else if(isset($_REQUEST['tipoTurnadoA']) && $_REQUEST['tipoTurnadoA'] == "abierto"){
                $turnadoA_id_txt = $_REQUEST['txtTurnadoA_abierto'];
            }

            $table = ($_REQUEST['tipoRegistro'] == "memo") ? "memo" : "oficio";

            $insertedId = DB::table( $table )->insertGetId(
                   [
                       "{$table}_tipo_turnado_a"     => $_REQUEST['tipoTurnadoA'], 
                       "{$table}_turnado_a_id_fk"    => $turnadoA_id_fk,
                       "{$table}_turnado_a_txt"      => $turnadoA_id_txt,
                       "{$table}_anio"               => $_REQUEST['tipoAnio'],
                       "{$table}_asunto"             => $_REQUEST['txtAsunto'],
                       "{$table}_observaciones"      => $_REQUEST['txtObservaciones'],
                       "{$table}_creador_id_fk"      => -1,
                       "{$table}_fecha_creacion"     => DB::raw('NOW()')
                   ]
            );
            
            if($insertedId == ""){
                 return Response()->json([
                    'msg'    => "Hubo un error al intentar registar el {$_REQUEST['tipoRegistro']} en la Base de Datos", 
                    'status' => "error",
                 ]);
            }
            
            return Response()->json([
                'msg'    => "El {$_REQUEST['tipoRegistro']} se registro Satisfactoriamente", 
                'status' => "success",
            ]);
        
    	} catch(\Illuminate\Database\QueryException $e){            
            return Response()->json(array('status' => 'error', 'msg'=>'Hubo un Error al registrar los datos','error'=>$e));
        }
    }
}
