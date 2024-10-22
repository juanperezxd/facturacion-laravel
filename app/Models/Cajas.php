<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cajas extends Model
{
    protected $table = "cajas";
    protected $fillable = [
        
        'tipomovimiento',
        'tiporelacion',
        'nombres',
        'descripcion',
        'fecha',
        'hora',
        'tipo_pago',
        'monto',
        'cierre',
        'user_id',
        'facturas_id',

    ];

    public function facturas(){
        return $this->belongsTo('App\Models\Facturas');
    }
}
