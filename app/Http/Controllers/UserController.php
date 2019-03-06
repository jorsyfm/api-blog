<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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
                'email' => 'required|email',
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
                // Conprobar si el usuario existe
                // Crear usuario

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
        // return "Adios";
    }

}
