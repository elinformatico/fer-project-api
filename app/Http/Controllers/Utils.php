<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use DB;
use App\Http\Controllers\Usuario;
use DateTime;

class Utils extends Controller
{
    public static function decodeUserId($userId) 
    {
        $encodedString = base64_decode($userId);
            $userId = explode("|", $encodedString);
            $userId = $userId[1];
        return $userId;
    }
    
    public static function isExpiredSession($dateExpired) 
    {
        $today_dt = new DateTime(date("Y-m-d G:i:s"));
        $expire_dt = new DateTime($dateExpired);

        return ($expire_dt < $today_dt);
    }
    
    public function validateSession($token) {
        $tokenValidation = Utils::validateApiToken($token)->getData();
        return Response()->json( $tokenValidation, $tokenValidation->status );
    }
  
    public function generateDevToken($username, $passInPlainText) {
        
        $passMd5 = md5($passInPlainText);
        
        $user = new Usuario();
        $userDataDB = $user->getUsuario($username, $passMd5);
        $userDataDB = $userDataDB->getData();
                        
        if($userDataDB->status == 'success') {
            
            $seek = env('SEED_TOKEN', '');
            $apiToken = Utils::generateAuthToken($seek, $userDataDB->token, $passMd5);
            
            return Response()->json(
                array('status' => 'success', 'msg'=> 'El fue autenticado correctamente!', 'token' => $apiToken),
                200
            );
        } else {
             return Response()->json(
                array('status' => 'error', 'msg'=> 'No se pudo autenticar el usuario'),
                401 
            );
        }
    }
    
    public static function generateAuthToken($seek, $token, $password) 
    {
        $apiToken = base64_encode(
            base64_encode($seek) . "||" . 
            base64_encode($token) . "||" . 
            base64_encode($password)
        );
        # $signApiToken = encrypt($apiToken);
        return $apiToken;
    }
    
    public static function validateApiToken($token = "") {
        
        $tokenAuthentication = "";
        
        if(!isset($_SERVER['HTTP_TOKEN']) )
        {    
            if($token == ""){
                return Response()->json(
                    array('status' => 401, 'msg' => "Lo sentimos! El Token es requerido para completar la solicitud!")
                );
            } else {
                $tokenAuthentication = $token;    
            }
            
        } else {
            if($_SERVER['HTTP_TOKEN'] == ""){
                return Response()->json(
                    array('status' => 401, 'msg' => "Lo sentimos! El Token no puede ir vacio!.")
                );
            } else {
                $tokenAuthentication = $_SERVER['HTTP_TOKEN'];        
            }
        }
        
        $errorMessageToken = "";
        $dataUser = [];
        
        # Token --> SEEK - DATATOKEN - PASSWORD
        # $apiTokenString = decrypt(base64_decode($tokenAuthentication));
        $apiTokenString = base64_decode($tokenAuthentication);
        $apiTokenObjects = explode("||", $apiTokenString);
        
        if(count($apiTokenObjects) == 3 && 
            isset($apiTokenObjects[0]) && 
            isset($apiTokenObjects[1]) && 
            isset($apiTokenObjects[2]))
        { 
            $seek = base64_decode($apiTokenObjects[0]);
            
            if($seek == env('SEED_TOKEN', ''))
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
                    
                    if(!Utils::isExpiredSession($dataUser['dateExpired'])) 
                    {   
                        $user = new Usuario();
                        $userDataDB = $user->getUsuario($dataUser['userName'], $passMd5);
                        $userDataDB = $userDataDB->getData();
                        
                        if($userDataDB->status == 'error') {            
                            return Response()->json(
                                array('status' => 401, 'msg'=> 'El usuario/password del Token no son validos')
                            );
                        }
                        
                    } else {
                        return Response()->json(
                            # array('status' => 401, 'msg'=> "El Token con fecha {$dataUser['dateExpired']} ha expirado. ServerTime: ".date("Y-m-d G:i:s"))
                            array('status' => 401, 'msg'=> "El Token ha expirado el {$dataUser['dateExpired']}")
                        ); 
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
  
    public static function generateDateString( $date ) {
        
    }
  
    public function registerLogin($jsonDataEncrypted) 
    {
        
        # $jsonDataDecrypted = base64_decode($jsonDataEncrypted);
        # $jsonDataDecoded = json_decode($jsonDataDecrypted);
        
        $dataEncrypted = encrypt("");
        
        return Response()->json(
            array('status' => 200, 'msg'=> 'Registro hecho con Exito --> ' . decrypt($dataEncrypted))
        ); 
    }
}
