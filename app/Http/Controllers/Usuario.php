<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use GuzzleHttp\Client;
use DB;

class Usuario extends Controller
{
    public function getUsuarios() 
    {
        try {
            $usuarios = DB::table('usuario')->get();
            return Response()->json(
                array(
                    'msg'=>'Vientos, todo jala chido', 
                    'usuarios' => $usuarios, 
                    "token" => "ZWxpbmZvcm1hdGljbw==|dXNlcg==|MjAxOC0wOC0wNCAxOjUyOjM5"
                )
            );
        } catch(\Illuminate\Database\QueryException $e){
            return Response()->json(
                array('msg'=>'Error de query al consultar los datos.','error'=>$e)
            );
        }
    }

    public function generateToken($username, $fullName, $typeUser, $minutesToExpire) 
    {
        $dateExpired = date("Y-m-d G:i:s", 
        mktime(date("G"), date("i") + $minutesToExpire, date("s"), date("m"), date("d"), date("Y"))); 

        $token = base64_encode($username) . "|" . base64_encode($fullName) . "|" . base64_encode($typeUser) . "|" . base64_encode($dateExpired);
        return $token;
    }

    public function getUsuario($username, $password) 
    {    
        try {
            $user = DB::table('usuario')
                            ->select(
                                "usr_nombre_usuario as username",
                                "usr_nombres as nombre",
                                "usr_apellido_paterno as paterno",
                                "usr_apellido_materno as materno",
                                "usr_tipo as typeUser"
                            )
                            ->where("usr_nombre_usuario", $username)
                            ->where("usr_password", $password)
                            ->first();

            if(isset($user->username))
            {
                $fullName = "{$user->nombre} {$user->paterno} {$user->materno}";

                $token = $this->generateToken($user->username, $fullName, $user->typeUser, 30);

                return Response()->json(
                    array(
                        'msg'       =>'Los datos de usuario se obtuvieron satisfactoriamente',            
                        "token"     => $token,
                        "status"    => "success",
                    )
                );
            } else {
                return Response()->json(
                    array(
                        'msg'       => "El usuario {$username} y/o password son incorrectos, por favor verifique los datos",
                        "token"     => "",
                        "status"    => "error",
                    )
                );
            }
            
        } catch(\Illuminate\Database\QueryException $e){
            return Response()->json(
                array('msg'=>'Error de query al consultar los datos.','error'=>$e)
            );
        }
    }

    public function consumeApi() 
    {
        
    }
}
