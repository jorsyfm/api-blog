<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Post extends Model {

    // Definir tabla a usar
    protected $table = 'posts';

    // Relación de 1 a muchos inversa (muchos a 1)
    public function user() {
        return $this->belongsTo('App\User', 'user_id');
    }

    // Relación de 1 a muchos inversa (muchos a 1)
    public function category() {
        return $this->belongsTo('App\Category', 'category_id');
    }
}
