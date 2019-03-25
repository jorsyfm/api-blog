<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Post;

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
        $posts = Post::all();

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'posts' => $posts
        ]);
    }

}