<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use DB;
use App\Http\Controllers\Usuario;

class Utils extends Controller
{
    public static function decodeUserId($userId) 
    {
        $encodedString = base64_decode($userId);
            $userId = explode("|", $encodedString);
            $userId = $userId[1];
        return $userId;
    }
    
    public static function isExpiredSession($dateExpired) {
        $currentTime = date("Y-m-d G:i:s"); 
        return strtotime($currentTime) < strtotime($dateExpired);
    }
    
    public static function validateApiToken() {
        
        if(!isset($_SERVER['HTTP_TOKEN'])) {
            return Response()->json(
                array('status' => 401, 'msg'=>'No se pudo autenticar la peticion por falta del Token')
            );
        }
        
        if($_SERVER['HTTP_TOKEN'] == "") {
            return Response()->json(
                array('status' => 401, 'msg'=>'El token es requerido y no puede ir vacio!')
            );
        }
        
        $errorMessageToken = "";
        $dataUser = [];
        
        # Token --> SEEK - DATATOKEN - PASSWORD
        $apiTokenString = base64_decode($_SERVER['HTTP_TOKEN']);
        $apiTokenObjects = explode("||", $apiTokenString);
        
        if(count($apiTokenObjects) == 3 && 
            isset($apiTokenObjects[0]) && 
            isset($apiTokenObjects[1]) && 
            isset($apiTokenObjects[2]))
        { 
            $seek = base64_decode($apiTokenObjects[0]);
            
            if($seek == "Fer$#@!2018!..")
            {    
                $dataToken = base64_decode($apiTokenObjects[1]);
                $passMd5   = base64_decode($apiTokenObjects[2]);

                $dataTokenObject = explode("|", $dataToken);

                if(count($dataTokenObject) == 5 && 
                   isset($dataTokenObject[0]) &&
                   isset($dataTokenObject[1]) &&
                   isset($dataTokenObject[2]) &&
                   isset($dataTokenObject[3]) &&
                   isset($dataTokenObject[4]) &&
                   is_numeric(base64_decode($dataTokenObject[0]))) 
                {    
                    $dataUser = [
                      "userId"        => base64_decode($dataTokenObject[0]),
                      "userName"      => base64_decode($dataTokenObject[1]),
                      "fullName"      => base64_decode($dataTokenObject[2]),
                      "role"          => base64_decode($dataTokenObject[3]),
                      "dateExpired"   => base64_decode($dataTokenObject[4]),
                    ];

                    $user = new Usuario();
                    $userDataDB = $user->getUsuario($dataUser['userName'], $passMd5);
                    $userDataDB = $userDataDB->getData();

                    if($userDataDB->status == 'error') {            
                        return Response()->json(
                            array('status' => 401, 'msg'=> 'El usuario/password del Token no son validos')
                        );
                    } else {
                        if(Utils::isExpiredSession($dataUser['dateExpired'])) {
                            return Response()->json(
                                array('status' => 408, 'msg'=> 'El Token ha expirado')
                            ); 
                        }
                    }

                } else {
                    $errorMessageToken = "error-token";
                }
            } else {
                $errorMessageToken = "error-token";
            }
            
        } else {
            $errorMessageToken = "error-token";
        }
        
        if($errorMessageToken != "error-token") {
            return Response()->json(
                array('status' => 200, 'msg'=> 'El token se ha validado Correctamente', 'data' => $dataUser)
            ); 
        } else {
            return Response()->json(
                array('status' => 401, 'msg'=>'Lo sentimos! El Token proporcionado no es valido')
            );  
        }
    }
  
    public static function getDataUser($userId) 
    {
        $dataUser = [];
        
        try {
            $usuario = DB::table('usuario')
                ->select(
                     "usr_id as user_id",
                     "usr_nombre_usuario as username",
                     "usr_nombres as nombre",
                     "usr_apellido_paterno as paterno",
                     "usr_apellido_materno as materno",
                     "usr_tipo as typeUser"
                 )
                 ->where("usr_id", "=", $userId)
                 ->first();
            
                if(isset($usuario->user_id)) {
                    $dataUser = $usuario;
                }
                return $usuario;
        
        } catch(\Illuminate\Database\QueryException $e){
            return $usuario;
        }
    }
  
    public static function querySingleData($table, $fields, $whereField, $whereData) 
    {
        $data = [];
        try {
            $result = DB::table($table)
                ->select($fields)
                ->where($whereField, "=", $whereData)
                ->first();
                return $result;
        
        } catch(\Illuminate\Database\QueryException $e){
            return $result;
        }
    }
}
