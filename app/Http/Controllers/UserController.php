<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use App\Helpers\JwtAuth;

class UserController extends Controller {

    // Register
    public function register(Request $request) {
        // return "Hola".$request['name'].$request->input('name');

        // Leer request
        $json_data = $request->input('json', null);
        $params = json_decode($json_data); // Objeto
        $params_array = json_decode($json_data, true); // Array

        // Validar que el request no venga vacío
        if (!empty($params_array)) {

            // Quitar espacios a los costados
            $params_array = array_map('trim', $params_array);

            // Validar datos
            $validate = \Validator::make($params_array, [
                'name' => 'required|alpha',
                'surname' => 'required|alpha',
                'email' => 'required|email|unique:users',
                'password' => 'required'
            ]);

            // Saber si pasó las validaciones
            if ($validate->fails()) {
                $data = array(
                    'status' => 'error',
                    'code' => 400,
                    'errors' => $validate->errors()
                );
            } else {

                // Cifrar la contraseña
                $params_array['password'] = hash('sha256',$params->password);

                // Crear usuario
                $user = new User();
                $user->name = $params_array['name'];
                $user->surname = $params_array['surname'];
                $user->email = $params_array['email'];
                $user->password = $params_array['password'];

                // Guardar usuario en la DB
                $user->save();

                // Usuario creado
                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'User created'
                );
            }

        } else {
            $data = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'Los campos no pueden estar vacios'
            );
        }

        return response()->json($data, $data['code']);
    }

    // Login
    public function login(Request $request) {

        // Leer request
        $json_data = $request->input('json', null);
        $data = json_decode($json_data, true); // Array

        // Validar email y password
        $validate = \Validator::make($data, [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if ($validate->fails()) {
            $response = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'El usuario no se ha podido identificar',
                'errors' => $validate->errors()
            );
        } else {
            $jwtAuth = new JwtAuth();
            $data['password'] = hash('sha256',$data['password']);
            if ( !empty($data['gettoken']) ) {
                $response = $jwtAuth->signup($data['email'],$data['password'],true);
            } else {
                $response = $jwtAuth->signup($data['email'],$data['password']);
            }
        }

        return response()->json($response,200);
    }

    public function update(Request $request) {
        
        // Recibir token
        $token = $request->header('Authorization');
        $jwtAuth = new \JwtAuth();
        $checkToken = $jwtAuth->checkToken($token);

        return response()->json($checkToken);

    }

}
