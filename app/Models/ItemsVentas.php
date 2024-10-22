<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemsVentas extends Model
{
    protected $table = "items_ventas";
    protected $fillable = [
        'ventas_id',
        'productos_id',
        'codigo_producto',
        'cantidad',
        'descripcion',
        'precio_unitario',
        'precio_mesa',
        'total',
    ];

    public function ventas(){
        return $this->belongsTo('App\Models\Ventas');
      }
}
