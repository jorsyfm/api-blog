<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Post;
use App\Helpers\JwtAuth;

class PostController extends Controller {
    
    /** 
     * Constructor para verificar Token en controladores
     */
    public function __construct() {
        $this->middleware('api.auth', ['except' => ['index', 'show']]);
    }

    /**
     * Traer todos los post
     */
    public function index() {
        $posts = Post::all()->load('category');

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'posts' => $posts
        ], 200);
    }

    /**
     * Traer la información de un post
     */
    public function show($id) {
        $post = Post::find($id)->load('category');

        if (is_object($post)) {
            $response = array(
                'code' => 200,
                'status' => 'success',
                'post' => $post
            );
        } else {
            $response = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'Post no encontrado'
            );
        }

        return response()->json($response, $response['code']);
    }

    /**
     * Guardar Post en DB
     */
    public function store(Request $request) {
        // Recibir datos
        $json_data = $request->input('json', null);
        $data = json_decode($json_data, true);

        if (!empty($data)) {

            // Conseguir usuario logueado
            $user = $this->getIdentity($request);

            // Validar datos
            $validate = \Validator::make($data, [
                'title' => 'required',
                'content' => 'required',
                'category_id' => 'required',
                'image' => 'required'
            ]);

            // Saber si pasa validación
            if ($validate->fails()) {
                $response = array(
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'Error al guardar post',
                );
            }  else {
                
                // Guardar Post
                $post = new Post();
                $post->user_id = $user->sub;
                $post->category_id = $data['category_id'];
                $post->title = $data['title'];
                $post->content = $data['content'];
                $post->image = $data['image'];
                $post->save();

                $response = array(
                    'code' => 200,
                    'status' => 'success',
                    'post' => $post
                );
            }
            

        } else {
            $response = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'Error al recibir información'
            );
        }

        return response()->json($response, $response['code']);
    }

    /**
     * Actualizar un post
     */
    public function update($id, Request $request) {

        // Recibir información
        $json_data = $request->input('json', null);
        $data = json_decode($json_data, true);

        // response por defecto
        $response = array(
            'code' => 400,
            'status' => 'error',
            'message' => 'No se ha recibido información para actualizar'
        );

        // Verificar que exista información enviada
        if (!empty($data)) {

            // Validar datos
            $validate = \Validator::make($data, [
                'title' => 'required',
                'content' => 'required',
                'category_id' => 'required'
            ]);

            // Saber si pasa las validaciones
            if ($validate->fails()) {
                $response['errors'] = $validate->errors();
                return response()->json($response, $response['code']);
            } else {

                // Borrar info que no se actualiza
                unset($data['id']);
                unset($data['user_id']);
                unset($data['created_at']);
                unset($data['user']);

                // Conseguir usuario logueado
                $user = $this->getIdentity($request);

                // Verificar que el post exista
                $post = Post::where([
                    ['id', $id],
                    ['user_id', $user->sub]
                ])->first();

                // Verificar que pueda actualizar el post
                if (is_object($post)) {
                    
                    // Actualizar
                    // $post = Post::where('id',$id)->updateOrCreate($data);
                    $post->update($data);

                    $response = array(
                        'code' => 200,
                        'status' => 'success',
                        'post' => $post
                    );

                } else {

                    $response = array(
                        'code' => 400,
                        'status' => 'error',
                        'message' => 'Error al tratar de actualizar post'
                    );

                }

            }
            
        }

        return response()->json($response, $response['code']);
    }

    /**
     * Borrar un post
     */
    public function destroy($id, Request $request) {

        // Conseguir usuario logueado
        $user = $this->getIdentity($request);

        // Saber que el ID no venga vacío
        if (!empty($id)) {

            // Verificar que el post exista
            $post = Post::where([
                ['id', $id],
                ['user_id', $user->sub]
            ])->first();

            if (is_object($post)) {
                    
                // Borrar post
                $post->delete();
                
                $response = array(
                    'code' => 200,
                    'status' => 'success',
                    'post' => $post
                );

            } else {

                $response = [
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'El post que intentas borrar no existe'
                ];

            }

            return response()->json($response, $response['code']);

        }
    }

    /**
     * Subir imágen para post
     */
    public function upload(Request $request) {
        // Recibir imágen
        $image = $request->file('file0');

        // Validar imágen
        $validate = \Validator::make($request->all(), [
            'file0' => 'required|image|mimes:jpg,jpeg,png,gif'
        ]);

        if (!$image || $validate->fails()) {
            $response = array(
                'code' => 400,
                'status' => 'error',
                'mesage' => 'Ocurrió un error al subir imagen'
            );
        } else {

            // Guardar imagen
            $image_name = time().$image->getClientOriginalName();

            \Storage::disk('images')->put($image_name, \File::get($image));
            $response = array(
                'code' => 200,
                'status' => 'succes',
                'image' => $image_name
            );

        }

        return response()->json($response, $response['code']);
    }

    /**
     * Retornar imagen de Post
     */
    public function getImage($filename) {

        // Saber si existe imagen
        $isset = \Storage::disk('images')->exists($filename);

        if ($isset) {
            // conseguir imagen
            $file = \Storage::disk('images')->get($filename);

            return new Response($file, 200);
        } else {
            $response = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'La imagen no existe'
            );
        }

        return response()->json($response, $response['code']);
    }

    /**
     * Conseguir usuario logueado
     */
    private function getIdentity($request) {
        // Conseguir usuario logueado
        $token = $request->header('Authorization');
        $jwtAuth = new JwtAuth();
        $user = $jwtAuth->checkToken($token, true);

        return $user;
    }
}