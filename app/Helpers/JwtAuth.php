<?php

namespace App\Helpers;

use Firebase\JWT\JWT;
use Illuminate\Support\Facades\DB;
use App\User;

class JwtAuth{

    public $key;

    public function __construct() {
        // Llave para JWT ('clavesecreta' en SHA-1)
        $this->key = 'c2c2a0636d7c840865f5ab9b07c295add69d1edc';
    }

    public function signup($email, $password, $getToken = null) {
        // Saber si existe el usuario
        $user = User::where([
            'email' => $email,
            'password' => $password
        ])->first();

        // Revisar si los datos son correctos
        $signup = false;
        if(is_object($user)) {
            $signup = true;
        }

        // Generar Token con los datos del usuario
        // iat sirve para recibir cuándo se creó el token
        // exp sirve para indicar cuanto tiempo dura el token, ejemplo 1 semana (7 dias * 24 horas * 60 minutos * 60 segundos)
        if($signup) {
            $token = array(
                'sub' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
                'surname' => $user->surname,
                'iat' => time(),
                'exp' => time() + (7 * 24 * 60 * 60)
            );

            // JWT token, llave(definida en el constructor), tipo de encriptado (opcional)
            $jwt = JWT::encode($token, $this->key, 'HS256');
            $decoded = JWT::decode($jwt, $this->key, ['HS256']);

            // Devoler datos del usuario(encriptados) o el token
            if(is_null($getToken)) {
                $data =  $jwt;
            }else{
                $data = $decoded;
            }
        }else{
            $data = array(
                'status' => 'error',
                'message' => 'Login incorrecto'
            );
        }

        return $data;
    }

    // Comprobar si el Token es correcto
    public function checkToken($jwt, $getIdentity = false) {

        $auth = false;

        // Probar token
        try {
            $jwt = str_replace('""','',$jwt);
            $decoded = JWT::decode($jwt, $this->key, ['HS256']);
        } catch (\UnexpectedValueException $e) {
            $auth = false;
        } catch (\DomainException $e) {
            $auth = false;
        }

        // Si el token es correcto
        if (!empty($decoded) && is_object($decoded) && isset($decoded->sub)) {
            $auth = true;
        } else {
            $auth = false;
        }

        if($getIdentity) {
            return $decoded;
        }

        return $auth;
    }
}
