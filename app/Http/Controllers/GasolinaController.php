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

                    $kilometraje = DB::table("registro_gasolina")
                                    ->select("rgas_kilometraje as kilometraje")
                                    ->join("car", "rgas_car_id_fk", "=", "car_id")
                                    ->where("car_id", $carId)
                                    ->max("rgas_kilometraje");

                    if($kilometraje != null)
                    {
                        return Response()->json(array(
                        'status' => 'success', 
                        'kilometraje' => $kilometraje, 
                        'msg' => 'Se obtuvo el kilometraje para el carro con ID ' + $carId));

                    } else {
                        return Response()->json(array(
                        'status' => 'error',
                        'msg' => "The Car Id [{$carId}] does not exits"));
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
