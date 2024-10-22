<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Movimientos extends Model
{
    protected $table = "movimientos";
    protected $fillable = [
        'id',
        'tipo_doc',
        'tipo_movimiento',
        'facturas_id',
        'clientes_id',
        'num_doc',
        'fecha',
        'tipo',
        'partida',
        'llegada',
        'placa',
        'licencia',
        'transportista',
        'ruc_trans',
        'fecha_traslado',
        'motivo_traslado',
        'peso_total',
        'unidad_medida_peso',
        'ruc_empresa',
        'direccion_empresa',
        'document_name',
        'ubigeo_partida',
        'ubigeo_llegada'
    ];

    public function facturas(){
        return $this->belongsTo('App\Models\Facturas', 'facturas_id');
    }
    public function clientes(){
        return $this->belongsTo('App\Models\Clientes', 'clientes_id');
    }
    public function itemMovimientos(){
    return $this->hasMany('App\Models\ItemMovimientos');
    }

}
