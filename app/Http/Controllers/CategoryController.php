<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Category;

class CategoryController extends Controller {

    /**
     * Constructor para proteger por autentificación
     */
    public function __construct() {
        $this->middleware('api.auth', ['except' => ['index', 'show']]);
    }
    
    /*
     * Obtener todas las categorías 
     */
    public function index() {
        $categories = Category::all();
        return response()->json([
            'code' => 200,
            'status' => 'success',
            'categories' => $categories
        ]);
    }

    /** 
     * Devolver una categoría
     */
    public function show($id) {
        $category = Category::find($id);

        if (is_object($category)) {
            $response = array(
                'code' => 200,
                'status' => 'success',
                'category' => $category
            );
        } else {
            $response = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'Categoría no encontrada'
            );
        }
        return response()->json($response, $response['code']);
    }

    /**
     * Crear categoría
     */
    public function store(Request $request) {
        // Obtener los datos post
        $json_data = $request->input('json', null);
        $data = json_decode($json_data, true);

        if (!empty($data)) {
            // Validar datos
            $validate = \Validator::make($data,[
                'name' => 'required'
            ]);

            // Si la validación falla
            if ($validate->fails()) {
                $response = array(
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'El nombre es obligatorio'
                );
            } else {
                $category = new Category();
                $category['name'] = $data['name'];
                $category->save();

                $response = array(
                    'code' => 200,
                    'status' => 'success',
                    'data' => $category
                );
            }
        } else {
            $response = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'No has enviado ninguna categoría'
            );
        }

        // Enviar respuesta
        return response()->json($response, $response['code']);
    }

    /**
     * Editar categoría
     */
    public function update($id, Request $request) {
        // Obtener los datos por post
        $json_data = $request->input('json', null);
        $data = json_decode($json_data, true);

        // Verificar que el parámetro no venga vacío
        if (!empty($data)) {

            // Validar datos
            $validate = \Validator::make($data, [
                'name' => 'required'
            ]);

            if ($validate->fails()) {
                $response = array(
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'Error al actualizar la categoría'
                );
            } else {
                // Quitar parámetros innecesarios
                unset($data['id']);
                unset($data['created_at']);
                unset($data['updated_at']);

                // Actualizar en DB
                $user_updated = Category::where('id',$id)->update($data);

                $response = array(
                    'code' => 200,
                    'status' => 'success',
                    'data' => $user_updated
                );
            }

        } else {
            $response = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'Error al actualizar la categoría'
            );
        }

        return response()->json($response, $response['code']);
    }
}
