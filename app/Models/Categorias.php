<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Categorias extends Model
{
    protected $table = "categorias";
    protected $fillable = [
        'nombre',
        'descripcion',
        'estado',
        'created_for',
        'updated_for'
    ];

    public function productos(){
        return $this->hasMany('App\Models\Productos');
    }
}
