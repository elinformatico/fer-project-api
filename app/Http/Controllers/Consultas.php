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
          
                $filterCorrespondencia = [];
                foreach($correspondencias as $value) 
                {
                    $strFechaLimite = "";
                    $colorStatus = "cor_green";
                    
                     # Formato: YYYY-MM-DD
                     $fechaLimiteDB = substr($value->fecha_limite, 0, 10);
                    
                    if($value->fecha_limite == "1990-01-01 00:00:00") {
                        $strFechaLimite = "Abierto";
                        $fechaLimiteDB = "_";
                    } else {
                        
                        # http://php.net/manual/es/function.date-diff.php
                        $databaseDate = date_create(substr($value->fecha_limite, 0, 10));
                        $currentData  = date_create(date("Y-m-d")); # Example: "2018-11-05 23:59:59" --> date("Y-m-d")
                        $diffDays = date_diff($databaseDate, $currentData, true);
                        
                        # Hoy > Fecha_Limite
                        if(strtotime(date("Y-m-d")) > strtotime($fechaLimiteDB)) {
                            $strFechaLimite = "Vencido hace " . $diffDays->format("%a") . " dia" . 
                                ( $diffDays->format("%a") == "1" ? "" : "s");
                            
                            $colorStatus = "cor_red_high";
                        } else {
                            
                            if(strtotime(date("Y-m-d")) == strtotime($fechaLimiteDB)) {
                                $strFechaLimite = "Vence Hoy";
                                $colorStatus = "cor_red_high";
                            } else {
                                
                                # Dia del vencimiento    --> RED-HIGH
                                # 1 dia al vencimiento   --> RED-LOW
                                # 2 dias al vencimiento  --> ORANGE
                                # 3 dias al vencimiento  --> YELLOW
                                # 4 o mas al vencimiento --> GREEN
                                
                                if($diffDays->format("%a") == "1") {
                                    $colorStatus = "cor_red_low";
                                } else if($diffDays->format("%a") == "2") {
                                    $colorStatus = "cor_orange";
                                } else if($diffDays->format("%a") == "3") {
                                    $colorStatus = "cor_yellow";
                                }
                                $strFechaLimite = $diffDays->format("%a") . " dias para vencer";    
                            }
                        }
                    }

                    $fechaLimiteDB = str_replace("-", "/", $fechaLimiteDB);
 
                    $row = [
                        "id"               => $value->id,
                        "folio"            => $value->folio,
                        "creador"          => $value->creador,
                        "persona_dirigida" => $value->persona_dirigida,
                        "fecha_creacion"   => $value->fecha_creacion,
                        "dirigido_a"       => $value->dirigido_a,
                        "depto_dirigido"   => $value->depto_dirigido,
                        "referencia"       => $value->referencia,
                        "observaciones"    => $value->observaciones,
                        "fecha_limite"     => $fechaLimiteDB,
                        "estatus_limite"   => $strFechaLimite,
                        "color_status"     => $colorStatus,
                    ];
                    array_push($filterCorrespondencia, $row);
                }
            
            
            if(count($filterCorrespondencia) > 0) {
                return Response()->json(
                    array(
                        'msg'              => "Se obtuvieron los datos correctamente", 
                        'correspondencias' => $filterCorrespondencia,
                        'status'           => "success",
                    )
                );
            } else {
                return Response()->json(
	                array(
	                    'msg'			    => 'No se encontraron correspondencias con los criterios de busqueda establecidos', 
	                    'correspondencias'  => [],
	                    'status'		    => "error",
	                )
	            );
            }
      
            
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
