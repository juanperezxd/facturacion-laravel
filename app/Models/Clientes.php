<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Clientes extends Model
{
    protected $table = "clientes";
    protected $fillable = [
        'tipo_documento',
        'documento',
        'razon_social',
        'direccion',
        'tipo_cliente',
        'created_for',
        'updated_for',
    ];

    public function movimientos(){
        return $this->hasMany('App\Models\Movimientos');
    }
}
