<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use DB;

class Utils extends Controller
{
    public static function decodeUserId($userId) 
    {
        $encodedString = base64_decode($userId);
            $userId = explode("|", $encodedString);
            $userId = $userId[1];
        return $userId;
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
