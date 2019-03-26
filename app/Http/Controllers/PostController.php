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

            // Conseguir usuario identificado
            // Token Authorization
            $token = $request->header('Authorization');
            $jwtAuth = new JwtAuth();
            $user = $jwtAuth->checkToken($token,true);

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

}