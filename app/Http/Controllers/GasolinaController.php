<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use DB;
use App\Http\Controllers\Utils;

class GasolinaController extends Controller
{
    public function registrarGasolina()
    {
        $tokenValidation = Utils::validateApiToken()->getData();
        if($tokenValidation->status == 200) 
        {
            try {

                $insertId = DB::table('registro_gasolina')->insertGetId(
                    [
                        'rgas_car_id_fk'    => $_REQUEST['carId'],
                        'rgas_litros'       => $_REQUEST['litros'], 
                        'rgas_tipoGasolina' => $_REQUEST['tipoGasolina'],
                        'rgas_monto'        => $_REQUEST['montoGasolina'],
                        'rgas_kilometraje'  => $_REQUEST['kilometraje'],
                        'rgas_fecha'        => DB::raw('NOW()'),
                    ]
                );

                return Response()->json(array('status' => 'success', 'msg' => "Se registro la carga de Gasolina correctamente."));
                //return response('Unauthorized.', 401);

            } catch(\Illuminate\Database\QueryException $e){            
                return Response()->json(array('status' => 'error', 'msg'=>'Se genero una excepcion al registrar la carga de Gasolina','error'=>$e));
            }   
        } else {
            return Response()->json( $tokenValidation, $tokenValidation->status );
        } 
    }

    public function getUltimoKilometrajeByCar($carId)
    {
        $tokenValidation = Utils::validateApiToken()->getData();
        if($tokenValidation->status == 200) 
        {
            try 
            {
                if($carId > 0){

                    $datosVehiculo = DB::table("registro_gasolina")
                                    # ->select("rgas_kilometraje as kilometraje")
                                    ->join("car", "rgas_car_id_fk", "=", "car_id")
                                    ->where("car_id", $carId)
                                    ->orderByRaw('rgas_id DESC LIMIT 2')
                                    ->get();
                    
                    if(count($datosVehiculo) > 0) 
                    {
                        $datosVehiculoCurr = $datosVehiculo[0];
                        $datosVehiculoPrev = $datosVehiculo[1];
                        
                        # TODO, Poner fecha de este campo ("Y-m-d G:i:s");
                        $dateChargeFuel = $datosVehiculoCurr->rgas_fecha; 
                        $pricePerLitre = round(($datosVehiculoCurr->rgas_monto / $datosVehiculoCurr->rgas_litros), 2);
                        $distancePrevCurr = $datosVehiculoCurr->rgas_kilometraje - $datosVehiculoPrev->rgas_kilometraje;
                        
                        $strMessage = "El dia {$dateChargeFuel} se cargó al Vehiculo {$datosVehiculoCurr->car_marca}, {$datosVehiculoCurr->car_submarca} " . 
                                      "la cantidad de {$datosVehiculoCurr->rgas_litros} Litros con un " . 
                                      "monto total de $ {$datosVehiculoCurr->rgas_monto} MXN, donde su precio por Litro fue de: $ {$pricePerLitre} MXN. Su ultimo " . 
                                      "Kilometraje registrado fue de {$datosVehiculoCurr->rgas_kilometraje} Kilometros. Distancia recorrida de la ultima carga {$distancePrevCurr} Km";
         
                        return Response()->json(
                            array(
                                'msg'               => "Se obtuvo la informacion del ultimo registro del Vehiculo {$datosVehiculoCurr->car_marca}, {$datosVehiculoCurr->car_submarca} ", 
                                'ultimoRegistro'    => $strMessage,
                                'ultimoKilometraje' => $datosVehiculoCurr->rgas_kilometraje,
                                "status"            => "success"
                            )
                        );
                        
                    } else {
                        return Response()->json(
                            array(
                                'msg'           => "No se encontro informacion del Vehiculo", 
                                "status"        => "error"
                            )
                        );
                    }
                } else {
                    return Response()->json(array(
                        'status' => 'error',
                        'msg' => 'El ID del carro no es valido'));
                }

            }catch(\Illuminate\Database\QueryException $e){
                return Response()->json(array('status' => 'error', 'msg'=>'Error on DB System','error'=>$e));
            }    
        } else {
            return Response()->json( $tokenValidation, $tokenValidation->status );
        }   
    }
  
    public function getTotalCurrentMonthAmount($carId) 
    {
        $tokenValidation = Utils::validateApiToken()->getData();
        if($tokenValidation->status == 200) 
        {
            try {
                
                # Getting the Last Day
                $currentDate = date("Y-m-d");
                $lastday = date('t',strtotime($currentDate));
 
                $datosVehiculo = DB::table("registro_gasolina")
                    ->select("rgas_monto as monto")
                    ->join("car", "rgas_car_id_fk", "=", "car_id")
                    ->where("car_id", $carId)
                    ->whereBetween("rgas_fecha", [ date("Y-m") . "-01 00:00:00", date("Y-m") . "-{$lastday} 23:59:59"])
                    ->get();
                
                $currentMonthAmount = 0;
                $chargesCount = 0;
                foreach($datosVehiculo as $vehiculo) 
                {
                    $currentMonthAmount += $vehiculo->monto;
                    $chargesCount++;  
                }
                
                $nameMonth = "";
                switch(date("m")) {
                    case "01": $nameMonth = "Enero"; break;
                    case "02": $nameMonth = "Febrero"; break;
                    case "03": $nameMonth = "Marzo"; break;
                    case "04": $nameMonth = "Abril"; break;
                    case "05": $nameMonth = "Mayo"; break;
                    case "06": $nameMonth = "Junio"; break;
                    case "07": $nameMonth = "Julio"; break;
                    case "08": $nameMonth = "Agosto"; break;
                    case "09": $nameMonth = "Septiembre"; break;
                    case "10": $nameMonth = "Octubre"; break;
                    case "11": $nameMonth = "Noviembre"; break;
                    case "12": $nameMonth = "Diciembre"; break;
                    default : break;
                }              
              
                return Response()->json(
                    array(
                        "msg"                => "En este mes de {$nameMonth} has gastado $ " . number_format($currentMonthAmount) . " Pesos y cargado {$chargesCount} veces gasolina",
                        "msgGastoMensual"    => $currentMonthAmount,
                        "numRecargasActual"  => $chargesCount,
                        "status"             => "success"
                    )
                );

            }catch(\Illuminate\Database\QueryException $e){
                    return Response()->json(array('status' => 'error', 'msg'=>'Error on DB System','error'=>$e));
            }
        } else {
            return Response()->json( $tokenValidation, $tokenValidation->status );
        }
    }
}
