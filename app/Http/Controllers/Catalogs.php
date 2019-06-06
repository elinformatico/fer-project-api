<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Utils;
use DB;

class Catalogs extends Controller
{
    public function getCategories() 
    {
        $tokenValidation = Utils::validateApiToken()->getData();
        if($tokenValidation->status == 200) 
        {
            try {

                $getCategories = DB::table('category')
                    ->orderBy('cat_name', 'asc')
                    ->get();

                if(count($getCategories) > 0){
                    return Response()->json(array(
                        'status' => 'success',
                        'categories' => $getCategories,
                        'msg' => 'The records of the categories were successfully obtained.'));
                } else {
                    return Response()->json(array(
                        'status' => 'error',
                        'msg'=>"There are no categories registered in the database."));
                }

            }catch(\Illuminate\Database\QueryException $e){
                return Response()->json(array('status' => 'error', 'msg'=>'Error on DB System','error'=>$e));
            }
        } else {
            return Response()->json( $tokenValidation, $tokenValidation->status );
        }
    }

    public function getPaymentMethods() 
    {    
        $tokenValidation = Utils::validateApiToken()->getData();
        if($tokenValidation->status == 200) 
        {
            try {

                $paymentMethods = DB::table('payment_method')
                    ->orderBy('pmt_name', 'asc')
                    ->get();

                if(count($paymentMethods) > 0){
                    return Response()->json(array(
                        'status' => 'success',
                        'paymentMethods' => $paymentMethods,
                        'msg' => 'The records of the payment methods were successfully obtained.'));
                } else {
                    return Response()->json(array(
                        'status' => 'error',
                        'msg'=>"There are no payment methods registered in the database."));
                }

            }catch(\Illuminate\Database\QueryException $e){
                return Response()->json(array('status' => 'error', 'msg'=>'Error on DB System','error'=>$e));
            }
        } else {
            return Response()->json( $tokenValidation, $tokenValidation->status );
        }
    }

    public function getPaymentMethodsByType($type) 
    {     
        $tokenValidation = Utils::validateApiToken()->getData();
        if($tokenValidation->status == 200) 
        {
            try {

                $paymentMethods = null;
                if($type == 'gasolina')
                {
                    $paymentMethods = DB::table('payment_method')
                    ->whereIn('pmt_name', 
                        [
                            'Tarjeta de Debido',
                            'Tarjeta de Credito', 
                            'Efectivo',
                            'Vales de Gasolina'
                        ])
                    ->orderBy('pmt_name', 'asc')
                    ->get();
                }

                if(count($paymentMethods) > 0){
                    return Response()->json(array(
                        'status' => 'success',
                        'paymentMethods' => $paymentMethods,
                        'msg' => 'The records of the payment methods were successfully obtained.'));
                } else {
                    return Response()->json(array(
                        'status' => 'error',
                        'msg'=>"There are no payment methods registered in the database."));
                }

            }catch(\Illuminate\Database\QueryException $e){
                return Response()->json(array('status' => 'error', 'msg'=>'Error on DB System','error'=>$e));
            }
        } else {
            return Response()->json( $tokenValidation, $tokenValidation->status );
        }
    }

    public function getBanks() 
    {
        $tokenValidation = Utils::validateApiToken()->getData();
        if($tokenValidation->status == 200) 
        {
            try {

                $banks = DB::table('bank')
                    # ->orderBy('bank_name', 'asc')
                    ->get();

                if(count($banks) > 0){
                    return Response()->json(array(
                        'status' => 'success',
                        'banks' => $banks,
                        'msg' => 'The records of the banks were successfully obtained.'));
                } else {
                    return Response()->json(array(
                        'status' => 'error',
                        'msg'=>"There are no banks registered in the database."));
                }

            }catch(\Illuminate\Database\QueryException $e){
                return Response()->json(array('status' => 'error', 'msg'=>'Error al ejecutar el query.','error'=>$e));
            }
        } else {
            return Response()->json( $tokenValidation, $tokenValidation->status );
        }          
    }

    public function getCars() 
    {
        $tokenValidation = Utils::validateApiToken()->getData();
        if($tokenValidation->status == 200) 
        {
            try {

                $cars = DB::table('car')
                    ->get();

                if(count($cars) > 0){
                    return Response()->json(array(
                        'status' => 'success',
                        'cars' => $cars,
                        'msg' => 'The records of the cars were successfully obtained.'));
                } else {
                    return Response()->json(array(
                        'status' => 'error',
                        'msg'=>"There are no cars registered in the database."));
                }

            }catch(\Illuminate\Database\QueryException $e){
                return Response()->json(array('status' => 'error', 'msg'=>'Error al ejecutar el query.','error'=>$e));
            }
        } else {
            return Response()->json( $tokenValidation, $tokenValidation->status );
        }
    }
}
