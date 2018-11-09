<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Utils;
use DB;

class Consultas extends Controller
{
    public function getCorrespondencia() 
    {
        try {
         
            $correspondencias = DB::table('correspondencia')
                ->select(
                    "cor_id as id",
                    DB::raw("CONCAT('C', cor_id) as folio"),
                    DB::raw("CONCAT(usr_nombres, ' ', usr_apellido_paterno, ' ', usr_apellido_materno) as creador"),
                    DB::raw(
                        "(SELECT CONCAT(usr_nombres, ' ', usr_apellido_paterno, ' ', usr_apellido_materno) 
                            FROM usuario WHERE usr_id = cor_dirigido_a_id_fk) as persona_dirigida"
                    ),
                    "cor_fecha_creacion as fecha_creacion",
                    "dpc_nombre as dirigido_a",
                    "dep_nombre as depto_dirigido",
                    "cor_referencia as referencia",
                    "cor_observaciones as observaciones",
                    "cor_limite_respuesta as fecha_limite"
                )
                ->leftjoin("usuario", "usr_id", "=", "cor_creador_id_fk")
                ->leftjoin("dependencia", "dpc_id", "=", "cor_dpc_id_fk")
                ->leftjoin("departamento", "dep_id", "=", "cor_dep_id_fk")
                ->where(function($query) {
        
                    if($_REQUEST["selectedUser"] == "true"){
                        $query->where("cor_creador_id_fk", "=", $_REQUEST['selectedUserId'] );
                    } else {
                      
                        $userData =  $userData = Utils::getDataUser(Utils::decodeUserId($_REQUEST['userId']));
                        if(isset($userData->typeUser) && $userData->typeUser != "admin") {
                            $query->where("cor_creador_id_fk", "=", Utils::decodeUserId($_REQUEST['userId']));    
                        }
                    }
         
                    if($_REQUEST['fechaInicial'] != "" && $_REQUEST['fechaFinal'] != "") 
                    {    
                        $fechaInicial = str_replace("/", "-", $_REQUEST['fechaInicial']) . " 00:00:00";
                        $fechaFinal   = str_replace("/", "-", $_REQUEST['fechaFinal']  ) . " 23:59:59";
                        
                        $query->where("cor_fecha_creacion", ">=", $fechaInicial);
                        $query->where("cor_fecha_creacion", "<=", $fechaFinal);
                    }
                })
                //->toSql();
                ->get();
      
            return Response()->json(
                array(
                    'msg'              => "Se obtuvieron los datos correctamente", 
                    'correspondencias' => $correspondencias,
                    'status'           => "success",
                )
            );
        } catch(\Illuminate\Database\QueryException $e){
            return Response()->json(
                array('msg'=>'Error de query al consultar los datos.','error'=>$e)
            );
        }
      
    }
  
    public function getCorrespondenciaByUser() {
    
    }
  
    public function getMemosCorrespondencia() {
        
    }
  
}
