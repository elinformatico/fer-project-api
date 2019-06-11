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
                                    ->orderByRaw('rgas_id DESC LIMIT 1')
                                    ->get();
                    
                    if(count($datosVehiculo) > 0) 
                    {
                        $datosVehiculo = $datosVehiculo[0];
                        
                        # TODO, Poner fecha de este campo ("Y-m-d G:i:s");
                        $dateChargeFuel = $datosVehiculo->rgas_fecha; 
                        $pricePerLitre = ($datosVehiculo->rgas_monto / $datosVehiculo->rgas_litros);
                        
                        $strMessage = "El dia {$dateChargeFuel} se cargó al Vehiculo {$datosVehiculo->car_marca}, {$datosVehiculo->car_submarca} " . 
                                      "la cantidad de {$datosVehiculo->rgas_litros} Litros con un " . 
                                      "monto total de $ {$datosVehiculo->rgas_monto} MXN, donde su precio por Litro fue de: $ {$pricePerLitre} MXN. Su ultimo " . 
                                      "Kilometraje registrado fue de: {$datosVehiculo->rgas_kilometraje} Kilometros.";
         
                        return Response()->json(
                            array(
                                'msg'               => "Se la informacion del ultimo registro del Vehiculo {$datosVehiculo->car_marca}, {$datosVehiculo->car_submarca} ", 
                                'ultimoRegistro'    => $strMessage,
                                'ultimoKilometraje' => $datosVehiculo->rgas_kilometraje,
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
}
