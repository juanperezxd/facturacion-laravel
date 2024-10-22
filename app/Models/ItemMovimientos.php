<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemMovimientos extends Model
{
    protected $table = "item_movimientos";
    protected $fillable = [
        'id',
        'movimientos_id',
        'productos_id',
        'cantidad',
        'saldo',
        'precio',
        'tipo'
    ];

    public function movimientos(){
        return $this->belongsTo('App\Models\Movimientos', 'movimientos_id');
    }
    public function productos(){
        return $this->belongsTo('App\Models\Productos', 'productos_id');
    }

    //SCOPE
    public function scopePorProducto($query,$producto_id){
        $query->with('movimientos')->where('productos_id',$producto_id)->whereDate('created_at', '>=', '2021-08-28')->orderBy('tipo','asc');
    }
    public function scopePorMovimiento($query,$movimiento_id){
        $query->with('movimientos')->where('movimientos_id',$movimiento_id)->orderBy('tipo','asc');
    }

    //reportes
    public function ScopekardexGeneral($query,$fecha_ini,$fecha_fin){
        return $query->orderBy('productos_id','asc')->with('movimientos')
        ->whereHas('movimientos',function($q) use($fecha_ini,$fecha_fin){
        $q->where('fecha','>=',$fecha_ini);
        $q->where('fecha','<=',$fecha_fin);
        });
    }
    public function ScopekardexGeneralProducto($query,$producto_id){
        return $query->orderBy('productos_id','asc')->with('movimientos')
                    ->where('productos_id',$producto_id);

    }
    public function scopekardexGeneralIngreso($query,$fecha_ini,$fecha_fin){
        return $query->orderBy('productos_id','asc')->with('movimientos')
        ->whereHas('movimientos',function($q) use($fecha_ini,$fecha_fin){
        $q->where('fecha','>=',$fecha_ini);
        $q->where('fecha','<=',$fecha_fin);
        $q->Where(function ($query) {
            $query->where('tipo','IN')
                ->orwhere('tipo','DE');
        });
        });
    }
    public function scopeIngresoProducto($query,$producto_id){
        return $query->orderBy('productos_id','asc')->with('movimientos')->where('productos_id',$producto_id)
        ->whereHas('movimientos',function($q){
        $q->Where(function ($query) {
            $query->where('tipo','IN')
                ->orwhere('tipo','DE');
        });
        });
    }
    public function scopekardexGeneralSalida($query,$fecha_ini,$fecha_fin){
        return $query->orderBy('productos_id','asc')->with('movimientos')
        ->whereHas('movimientos',function($q) use($fecha_ini,$fecha_fin){
        $q->where('fecha','>=',$fecha_ini);
        $q->where('fecha','<=',$fecha_fin);
        $q->where('tipo','RE');
        });
    }
    public function scopesalidaProducto($query,$producto_id){
        return $query->orderBy('productos_id','asc')->with('movimientos')->where('productos_id',$producto_id)
        ->whereHas('movimientos',function($q){
        $q->where('tipo','RE');
        });
    }
    public function scopeagrupadoColaborador($query,$colaboradores_id){
        return $query->orderBy('productos_id','asc')->with('movimientos')
        ->whereHas('movimientos',function($q) use($colaboradores_id){
        $q->where('colaboradores_id',$colaboradores_id);
        $q->where('tipo','RE');
        });
    }
}
