<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Productos extends Model
{
    protected $table = "productos";
    protected $fillable = [
        'id',
        'nombre',
        'codigo',
        'stock',
        'impuestos_id',
        'unidades_id',
        'categorias_id',
        'precio_venta',
        'precio_compra',
        'imagen',
        'created_for',
        'updated_for',
        'codigo_barra',
        'precio_mesa'
    ];

    public function categorias(){
        return $this->belongsTo('App\Models\Categorias');
    }
    public function unidades(){
        return $this->belongsTo('App\Models\Unidades');
    }
    public function impuestos(){
        return $this->belongsTo('App\Models\Impuestos');
    }

    public function itemMovimientos(){
        return $this->hasMany('App\Models\ItemMovimientos');
    }
}
