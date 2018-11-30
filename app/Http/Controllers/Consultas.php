<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Utils;
use DB;
use Response;
use App\Http\Controllers\PdfWrapper as PDF;

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
                      
                        $userData = $userData = Utils::getDataUser(Utils::decodeUserId($_REQUEST['userId']));
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
                        'linkPdf'          => $this->generateLinkPdf(),
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
  
    public function getMemosOficios() {
        
        try {
            
            $table =  str_replace("s", "", $_REQUEST['buscarPor']);
            $inicial = ($_REQUEST['buscarPor'] === "memos" ? "M" : "O");
            
            $resultados = DB::table($table)
                ->select(
                    "{$table}_id as id",
                    "{$table}_tipo_turnado_a as tipo",
                    DB::raw("CONCAT('{$inicial}', {$table}_id) as folio"),
                    DB::raw("CONCAT(usr_nombres, ' ', usr_apellido_paterno, ' ', usr_apellido_materno) as creador"),
                    "{$table}_turnado_a_id_fk as turnado_a_id",
                    "{$table}_turnado_a_txt as abierto_txt",
                    "{$table}_anio as anio",
                    "{$table}_asunto as asunto",
                    "{$table}_observaciones as observaciones",
                    "{$table}_fecha_creacion as fecha_creacion"
                )
                ->leftjoin("usuario", "usr_id", "=", "{$table}_creador_id_fk")
                ->where(function($query) {
                    
                    $table =  str_replace("s", "", $_REQUEST['buscarPor']);
                    
                    if($_REQUEST["selectedUser"] == "true"){
                        $query->where("{$table}_creador_id_fk", "=", $_REQUEST['selectedUserId'] );
                    } else {
                      
                        $userData = $userData = Utils::getDataUser(Utils::decodeUserId($_REQUEST['userId']));
                        if(isset($userData->typeUser) && $userData->typeUser != "admin") {
                            $query->where("{$table}_creador_id_fk", "=", Utils::decodeUserId($_REQUEST['userId']));    
                        }
                    }
         
                    if($_REQUEST['fechaInicial'] != "" && $_REQUEST['fechaFinal'] != "") 
                    {    
                        $fechaInicial = str_replace("/", "-", $_REQUEST['fechaInicial']) . " 00:00:00";
                        $fechaFinal   = str_replace("/", "-", $_REQUEST['fechaFinal']  ) . " 23:59:59";
                        
                        $query->where("{$table}_fecha_creacion", ">=", $fechaInicial);
                        $query->where("{$table}_fecha_creacion", "<=", $fechaFinal);
                    }
                })
                ->get();
            
                $filterResultados = [];
                foreach($resultados as $value) 
                {
                    $turnadoA_nombre = "";
                    
                    if($value->tipo === "abierto") {
                        $turnadoA_nombre = $value->abierto_txt;
                        
                    } else if($value->tipo === "usuario") {
                        
                        $usuario = Utils::querySingleData("usuario", [
                            DB::raw("CONCAT(usr_nombres, ' ', usr_apellido_paterno, ' ', usr_apellido_materno) as usuario")
                        ], "usr_id", $value->turnado_a_id);
                        
                        if(isset($usuario->usuario)) {
                            $turnadoA_nombre = $usuario->usuario;
                        }
                        
                    # Dependencia buscara por el ID de la Correspondencia
                    } else if($value->tipo === "dependencia") {
                        
                        $dep = Utils::querySingleData("correspondencia", [
                            DB::raw("CONCAT('C', cor_id) as folio"),
                            "cor_referencia as referencia",
                             DB::raw(
                                "(SELECT dpc_nombre
                                  FROM dependencia WHERE dpc_id = cor_dpc_id_fk) as dependencia"
                            ),
                        ], "cor_id", $value->turnado_a_id);
                        
                        if(isset($dep->folio) && isset($dep->referencia)) {
                            $turnadoA_nombre = "Folio: " . $dep->folio . ", " . $dep->referencia . ", " . $dep->dependencia;
                        }
                    }
                    
                    $fechaCreacion = substr($value->fecha_creacion, 0, 10);
                    $anio = str_replace("anio_", "", $value->anio);
                    
                    $row = [
                        "id"               => $value->id,
                        "tipo"             => $value->tipo,
                        "tabla"            => ucfirst($table), # Capital Letter
                        "folio"            => $value->folio,
                        "creador"          => $value->creador,
                        "turnado_a"        => $turnadoA_nombre,
                        "abierto_txt"      => $value->abierto_txt,
                        "anio"             => ucfirst($anio), # Capital Letter
                        "asunto"           => $value->asunto,
                        "observaciones"    => $value->observaciones,
                        "fecha_creacion"   => $fechaCreacion,
                    ];
                    array_push($filterResultados, $row);  
                }
            
                if(count($resultados) > 0) {
                    return Response()->json(
                        array(
                            'msg'              => "Se obtuvieron los datos correctamente para {$table}", 
                            'resultados'       => $filterResultados,
                            'linkPdf'          => $this->generateLinkPdf(),
                            'status'           => "success",
                        )
                    );
                } else {
                    return Response()->json(
                        array(
                            'msg'			    => 'No se encontraron correspondencias con los criterios de busqueda establecidos', 
                            'resultados'        => [],
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
    
    public function generateLinkPdf() 
    {
        $selectedUserId = ($_REQUEST["selectedUser"] == "true") 
          ? $_REQUEST['selectedUserId'] 
          : $_REQUEST['userId'];
      
        $link  = "?userId={$_REQUEST['userId']}&";
        $link .= "selectedUser={$_REQUEST['selectedUser']}&";
        $link .= "selectedUserId={$selectedUserId}&";
        $link .= "fechaInicial={$_REQUEST['fechaInicial']}&";
        $link .= "fechaFinal={$_REQUEST['fechaFinal']}&";
        $link .= "buscarPor={$_REQUEST['buscarPor']}";
        
        return $link;
    }
    
    private function getDataForPdfAndCsv() 
    {    
        $results = [];
        $linkPdf = "";
        
        if(isset($_REQUEST['buscarPor']) && ($_REQUEST['buscarPor'] === "memos" || $_REQUEST['buscarPor'] === "oficios")) 
        {    
            // Obtenemos los datos de la funcion con todos los parametros seteados por la funcion "generateLinkPdf"
            $results = $this->getMemosOficios()->getData();
            $linkPdf = $results->linkPdf;
            $results = $results->resultados;

        } else if(isset($_REQUEST['buscarPor']) && $_REQUEST['buscarPor'] === "correspondencia") {
            
            $results = $this->getCorrespondencia()->getData();
            $linkPdf = $results->linkPdf;
            $results = $results->correspondencias;
        }
        
        $data =  [
            'title'         => "Resultados de {$_REQUEST['buscarPor']}",
            'description'   => 'Nombre de la Empresa',
            'type'          => $_REQUEST['buscarPor'],
            'fechaCreacion' => date("Y-m-d") . " a las " . date("H:i:s") . " horas",
            'results'       => $results,
            'linkPdf'       => $linkPdf
        ];
        
        return $data;
    }
    
    public function generatePdf() 
    {        
        # Seteamos la Zona Horaria para la fecha de creacion del documento
        date_default_timezone_set('America/Mexico_City');
        
        $data = $this->getDataForPdfAndCsv();
        # echo "<pre>"; print_r($data); echo "</pre>"; exit;
        
        if(count($data['results']) > 0) {
             // mPDF --> https://todoconk.com/2016/02/23/como-crear-archivos-pdf-con-php/
            $pdf = new PDF('utf-8');
            $header = \View::make('consultas')->render();
            $pdf->loadView('consultas', ['data' => $data]);
            # $pdf->download('consultas.pdf');    # <--- Opcion para descargar directamente el PDF
            $pdf->stream('consultas.pdf');      # <--- Opcion para visualizar el PDF en el navegador     
        } else {
            echo "<h2>No hay resultados para poder generar el PDF!</h2>";   
        }
    }
    
    public function generateCSV() 
    {    
        $data = $this->getDataForPdfAndCsv();
        $results = $data['results'];
        $dataType = $data['type'];
        $columns = [];
        
        $headers = array(
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=" . ucfirst($dataType) . "_" . date("Y-m-d") . ".csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        );
        
        # echo "<pre>"; print_r($data); echo "</pre>"; exit;
        
        if($dataType == "memos" || $dataType == "oficios")  {
            $columns = array('Folio', 'Tipo', 'Creado_Por', 'Turnado_A', 'Anio', 'Asunto', 'Observaciones', 'Fecha Creacion');    
            
        } else if($dataType == "correspondencia") {
            $columns = array('Folio', 'Tipo', 'Fecha_Creado', 'Solicitante', 'Dirigido_A', 'Depto_Dirigido', 'Usuario_Dirigido', 'Referencia', 'Fecha_Limite', 'Estatus');
        }

        $callback = function() use ($results, $columns, $dataType)
        {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach($results as $row) {
                
                if($dataType == "memos" || $dataType == "oficios") 
                {
                    fputcsv($file, [
                            $row->folio, 
                            $row->tabla, 
                            utf8_decode($row->creador), 
                            utf8_decode($row->turnado_a), 
                            $row->anio, 
                            utf8_decode($row->asunto), 
                            utf8_decode($row->observaciones), 
                            $row->fecha_creacion
                        ]
                    ); 
                } else if($dataType == "correspondencia") {
                    
                    fputcsv($file, [
                            $row->folio, 
                            $dataType, 
                            $row->fecha_creacion, 
                            utf8_decode($row->creador), 
                            utf8_decode($row->dirigido_a), 
                            utf8_decode($row->depto_dirigido), 
                            utf8_decode($row->persona_dirigida), 
                            utf8_decode($row->referencia),
                            $row->fecha_limite,
                            utf8_decode($row->estatus_limite)
                        ]
                    );
                }
            }
            fclose($file);
        };
        
        return Response::stream($callback, 200, $headers);
    }
}
