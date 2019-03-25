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

    // Actualizar información
    public function update(Request $request) {        

        // Recibir datos por Post
        $json_data = $request->input('json', null);
        $params = json_decode($json_data,true); // Array

        // Actualizar Usuario
        if (!empty($params)) {

            // Obtener datos del usuario identificado
            $token = $request->header('Authorization');
            $jwtAuth = new \JwtAuth();
            $user = $jwtAuth->checkToken($token,true);

            // Validar datos
            $validate = \Validator::make($params, [
                'name' => 'required|alpha',
                'surname' => 'required|alpha',
                'email' => 'required|email|unique:users,'.$user->sub
            ]);

            // Quitar los campos que no se van a actualizar
            unset($params['id']);
            unset($params['role']);
            unset($params['password']);
            unset($params['created_at']);
            unset($params['remember_token']); 

            // Actualizar datos
            $user_update = User::where('id', $user->sub)->update($params);

            // Response
            $response = array(
                'code' => 200,
                'status' => 'success',
                'user' => $user_update,
                'changes' => $params
            );

        } else {
            // Error al identificarse
            $response = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'El usuario no está identificado'
            );
        }

        return response()->json($response,$response['code']);

    }

    // Subir imágen
    public function upload(Request $request) {
        // Recibir datos
        $image = $request->file('file0');

        // Guardar imagen
        if ($image) {
            $image_name = time().$image->getClientOriginalName();
            \Storage::disk('users')->put($image_name, \File::get($image));
            // Status correcto
            $response = array(
                'code' => 200,
                'image' => $image_name,
                'status' => 'success'
            );
        } else {
            // Status error
            $response = array(
                'code' => 200,
                'status' => 'error',
                'message' => 'Error al subir imagen'
            );
        }
        
        return response()->json($response,$response['code']);
    }

}
