<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Items extends Model
{
    protected $table='items';
    protected $fillable = [
        'id',
        'facturas_id',
        'productos_id',
        'setCodProducto',
        'setUnidad',
        'setCantidad',
        'setDescripcion',
        'setMtoBaseIgv',
        'setPorcentajeIgv',
        'setIgv',
        'setTipAfeIgv',
        'setTotalImpuestos',
        'setMtoValorVenta',
        'setMtoValorUnitario',
        'setMtoPrecioUnitario',

    ];
  public function facturas(){
    return $this->belongsTo('App\Models\Facturas');
  }
}
