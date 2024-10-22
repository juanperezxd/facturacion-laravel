<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gastos extends Model
{
    protected $table = "gastos";
    protected $fillable = [
        'fecha',
        'descripcion',
        'monto',
        'comprobante',
        'documento',
        'razon_social',
        'tipoComprobante',
        'nroComprobante',
        'caja',
        'user_id'
    ];

    public function users(){
        return $this->belongsTo('App\Models\User', 'user_id');
    }
}
