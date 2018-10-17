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

    public function getNombresUsuarios() 
    {
        try {
            $usuarios = DB::table('usuario')
                            ->select(
                                DB::raw("CONCAT(usr_nombres, ' ', usr_apellido_paterno, ' ', usr_apellido_materno) as nombre"),
                                "usr_id as id",
                                "dep_nombre as departamento"
                            )
                            ->join('jefe_departamento', 'jef_usr_id_fk', '=', 'usr_id')
                            ->join('departamento', 'dep_id', '=', 'jef_dep_id_fk')
                            ->get();
            return Response()->json(
                array(
                    'msg'=>'Se obtuvieron todos los usuarioa', 
                    'usuarios' => $usuarios,
                    "status"    => "success"
                )
            );
        } catch(\Illuminate\Database\QueryException $e){
            return Response()->json(
                array('msg'=>'Error al obtener los usuarios','error'=>$e)
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

    public function registrarUsuario() 
    {
        try {

            # Verificar que no existe nombre de Usuario
            $nombreUsuario = DB::table("usuario")
                                        ->select("usr_nombre_usuario as username")
                                        ->where("usr_nombre_usuario", $_REQUEST['nombreUsuario'])
                                        ->first();

            $departamentoId = $_REQUEST['selectedDepartamento'];

            if(!isset($nombreUsuario->username)) {

                $userId = DB::table('usuario')->insertGetId(
                    [
                        'usr_nombre_usuario'    => $_REQUEST['nombreUsuario'],
                        'usr_nombres'           => $_REQUEST['nombre'], 
                        'usr_apellido_paterno'  => $_REQUEST['apellidoPaterno'],
                        'usr_apellido_materno'  => $_REQUEST['apellidoMaterno'],
                        'usr_password'          => md5($_REQUEST['password']),
                        'usr_tipo'              => $_REQUEST['rolUsuario'],
                        'usr_fecha_creacion'    => DB::raw('NOW()'),
                    ]
                );

                if($userId != -1){
                    if($_REQUEST["esJefe"] == "true")
                    {
                        $jefeId = DB::table("jefe_departamento")->insert(
                            [
                                "jef_dep_id_fk" => $departamentoId,
                                "jef_usr_id_fk" => $userId,
                            ]
                        );
                    }

                    return Response()->json(array('status' => 'success', 'msg' => "Se registro el Usuario correctamente."));
                } else {
                    return Response()->json(array('status' => 'error', 'msg' => "No se pudo registrar el Usuario."));
                }

            } else {
                return Response()->json(array('status' => 'error', 'msg' => "El nombre de Usuario --> [{$_REQUEST['nombreUsuario']}] ya se encuentra registrado en el Sistema, porfavor eligue otro diferente!"));
            }

        } catch(\Illuminate\Database\QueryException $e){            
            return Response()->json(array('status' => 'error', 'msg'=>'Hubo un Error al registrar los datos','error'=>$e));
        }
    }

    public function consumeApi() 
    {
        
    }
}
