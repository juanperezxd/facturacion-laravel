<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ventas extends Model
{
    protected $table = "ventas";
    protected $fillable = [
        'clientes_id',
        'mesas_id',
        'mesas_id',
        'fecha_pago',
        'tipo_comprobante',
        'tipo_pago',
        'cod_referencia',
        'valor_total',
        'user_id'
    ];

    public function items_ventas(){
        return $this->hasMany('App\Models\ItemsVentas');
    }

    public function mesas(){
        return $this->belongsTo('App\Models\Mesas');
    }

    public function user(){
        return $this->belongsTo('App\Models\User', 'user_id');
    }
}
