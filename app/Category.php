<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Category extends Model {

    // Definir tabla a usar
    protected $table = 'categories';

    // Relación 1 a muchos
    public function posts() {
        return $this->hasMany('App\Post');
    }
}
