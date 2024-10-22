<?php

namespace App\Http\Controllers\Api\web\reportes;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use DB;
//REPORTE
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ReporteInventariosExport;

use App\Models\ItemMovimientos;
use App\Models\Productos;


class InventariosController extends Controller
{
    public function kardexGeneral(Request $request){
        $fecha_ini=$request->fecha_inicio;
        $fecha_fin=$request->fecha_fin;
        $productos=ItemMovimientos::kardexGeneral($fecha_ini,$fecha_fin)->get();
        $data=[];
        foreach($productos as $producto){
          $tipo=$producto->movimientos->tipo;
          if($tipo=="IN" or $tipo=="DE"){$saldo=$producto->saldo;}else{$saldo='('.$producto->saldo.')';}
          if($tipo=="IN"){
              $tipo="INGRESO";
          }else if($tipo=="RE"){
              
              if ($producto->movimientos->tipo_movimiento == 'VENTA') {
                  $tipo = 'VENTA';
              }else {
                $tipo="RETIRO";
              }

          }else if($tipo=="DE"){
              $tipo="DEVOLUCION";
          }
          $data[]=[
            'tipo_doc' => $producto->movimientos->tipo_doc,
            'num_doc' => $producto->movimientos->num_doc,
            'responsable' => $producto->movimientos->clientes->razon_social,
            'fecha' => $producto->movimientos->fecha,
            'tipo' => $tipo,
            'descripcion' => $producto->productos->nombre,
            'cantidad' => $producto->cantidad,
            'saldo' => $saldo,
          ];
        }
        return response()->json(array(
            'datos' => $data,
        ));
    }

    public function kardexGeneralProducto(Request $request){
        $producto_id=$request->producto['id'];
        $productos=ItemMovimientos::kardexGeneralProducto($producto_id)->get();
        $data=[];
        foreach($productos as $producto){
          $tipo=$producto->movimientos->tipo;
          if($tipo=="IN" or $tipo=="DE"){$saldo=$producto->saldo;}else{$saldo='('.$producto->saldo.')';}
          if($tipo=="IN"){
            $tipo="INGRESO";
            }else if($tipo=="RE"){
                
                if ($producto->movimientos->tipo_movimiento == 'VENTA') {
                    $tipo = 'VENTA';
                }else {
                $tipo="RETIRO";
                }

            }else if($tipo=="DE"){
                $tipo="DEVOLUCION";
            }
          $data[]=[
            'tipo_doc' => $producto->movimientos->tipo_doc,
            'num_doc' => $producto->movimientos->num_doc,
            'responsable' => $producto->movimientos->clientes->razon_social,
            'fecha' => $producto->movimientos->fecha,
            'tipo' => $tipo,
            'descripcion' => $producto->productos->nombre,
            'cantidad' => $producto->cantidad,
            'saldo' => $saldo,
          ];
        }
        return response()->json(array(
            'datos' => $data,
        ));
    }

    public function stockProducto(){
        $productos=Productos::all();
        $data=[];
        foreach($productos as $producto){
          $data[]=[
            'codigo' => $producto->codigo,
            'descripcion' => $producto->nombre,
            'categoria' => $producto->categorias->nombre,
            'unidad' => $producto->unidades->nombre,
            'stock' => $producto->stock,
            'precio' => 'S/. '.$producto->precio_venta,
          ];
        }
        return response()->json(array(
            'datos' => $data,
        ));
    }

    public function ingresosFecha(Request $request){
        $fecha_ini=$request->fecha_inicio;
        $fecha_fin=$request->fecha_fin;
        $productos=ItemMovimientos::kardexGeneralIngreso($fecha_ini,$fecha_fin)->get();
        $data=[];
        foreach($productos as $producto){
          $tipo=$producto->movimientos->tipo;
          if($tipo=="IN" or $tipo=="DE"){$saldo=$producto->saldo;}else{$saldo='('.$producto->saldo.')';}
          if($tipo=="IN"){
            $tipo="INGRESO";
        }else if($tipo=="RE"){
            
            if ($producto->movimientos->tipo_movimiento == 'VENTA') {
                $tipo = 'VENTA';
            }else {
              $tipo="RETIRO";
            }

        }else if($tipo=="DE"){
            $tipo="DEVOLUCION";
        }

          $data[]=[
            'tipo_doc' => $producto->movimientos->tipo_doc,
            'num_doc' => $producto->movimientos->num_doc,
            'responsable' => $producto->movimientos->clientes->razon_social,
            'fecha' => $producto->movimientos->fecha,
            'tipo' => $tipo,
            'descripcion' => $producto->productos->nombre,
            'cantidad' => $producto->cantidad,
            'saldo' => $saldo,
          ];
        }
        return response()->json(array(
            'datos' => $data,
        ));
    }

    public function ingresoAgrupadoProducto(Request $request){
        $producto_id=$request->producto['id'];
        $productos=ItemMovimientos::IngresoProducto($producto_id)->get();
        $data=[];
        foreach($productos as $producto){
          $tipo=$producto->movimientos->tipo;
          if($tipo=="IN" or $tipo=="DE"){$saldo=$producto->saldo;}else{$saldo='('.$producto->saldo.')';}
          
          if($tipo=="IN"){
            $tipo="INGRESO";
        }else if($tipo=="RE"){
            
            if ($producto->movimientos->tipo_movimiento == 'VENTA') {
                $tipo = 'VENTA';
            }else {
              $tipo="RETIRO";
            }

        }else if($tipo=="DE"){
            $tipo="DEVOLUCION";
        }

          $data[]=[
            'tipo_doc' => $producto->movimientos->tipo_doc,
            'num_doc' => $producto->movimientos->num_doc,
            'responsable' => $producto->movimientos->clientes->razon_social,
            'fecha' => $producto->movimientos->fecha,
            'tipo' => $tipo,
            'descripcion' => $producto->productos->nombre,
            'cantidad' => $producto->cantidad,
            'saldo' => $saldo,
          ];
        }
        return response()->json(array(
            'datos' => $data,
        ));
    }

    public function salidasFecha(Request $request){
        $fecha_ini=$request->fecha_inicio;
        $fecha_fin=$request->fecha_fin;
        $productos=ItemMovimientos::kardexGeneralSalida($fecha_ini,$fecha_fin)->get();
        $data=[];
        foreach($productos as $producto){
          $tipo=$producto->movimientos->tipo;
          if($tipo=="IN" or $tipo=="DE"){$saldo=$producto->saldo;}else{$saldo='('.$producto->saldo.')';}
          
          if($tipo=="IN"){
            $tipo="INGRESO";
        }else if($tipo=="RE"){
            
            if ($producto->movimientos->tipo_movimiento == 'VENTA') {
                $tipo = 'VENTA';
            }else {
              $tipo="RETIRO";
            }

        }else if($tipo=="DE"){
            $tipo="DEVOLUCION";
        }

          $data[]=[
            'tipo_doc' => $producto->movimientos->tipo_doc,
            'num_doc' => $producto->movimientos->num_doc,
            'responsable' => $producto->movimientos->clientes->razon_social,
            'fecha' => $producto->movimientos->fecha,
            'tipo' => $tipo,
            'descripcion' => $producto->productos->nombre,
            'cantidad' => $producto->cantidad,
            'saldo' => $saldo,
          ];
        }
        return response()->json(array(
            'datos' => $data,
        ));
    }

    public function salidasAgrupadoProducto(Request $request){
        $producto_id=$request->producto['id'];
        $productos=ItemMovimientos::salidaProducto($producto_id)->get();
        $data=[];
        foreach($productos as $producto){
          $tipo=$producto->movimientos->tipo;
          if($tipo=="IN" or $tipo=="DE"){$saldo=$producto->saldo;}else{$saldo='('.$producto->saldo.')';}
          
          if($tipo=="IN"){
            $tipo="INGRESO";
        }else if($tipo=="RE"){
            
            if ($producto->movimientos->tipo_movimiento == 'VENTA') {
                $tipo = 'VENTA';
            }else {
              $tipo="RETIRO";
            }

        }else if($tipo=="DE"){
            $tipo="DEVOLUCION";
        }

          $data[]=[
            'tipo_doc' => $producto->movimientos->tipo_doc,
            'num_doc' => $producto->movimientos->num_doc,
            'responsable' => $producto->movimientos->clientes->razon_social,
            'fecha' => $producto->movimientos->fecha,
            'tipo' => $tipo,
            'descripcion' => $producto->productos->nombre,
            'cantidad' => $producto->cantidad,
            'saldo' => $saldo,
          ];
        }
        return response()->json(array(
            'datos' => $data,
        ));
    }


    //excel
    //REPORTES
    public function reportes($reporte,$producto, $fecha_desde, $fecha_hasta){
        $coleccion = '';
        $titulo = '';
        //REPORTES BOLSA ASIGNADA POR FECHA
        if ($reporte == 'kardex_general_fecha'){

            $coleccion = DB::table('item_movimientos')
                        ->join('movimientos', 'item_movimientos.movimientos_id', '=', 'movimientos.id')
                        ->join('productos', 'item_movimientos.productos_id', '=', 'productos.id')
                        ->join('clientes', 'movimientos.clientes_id', '=', 'clientes.id')
                        ->where('movimientos.fecha', '>=', $fecha_desde)
                        ->where('movimientos.fecha', '<=', $fecha_hasta)
                        ->select(
                            'movimientos.tipo_doc',
                            'movimientos.tipo_movimiento',
                            'movimientos.num_doc',
                            'clientes.razon_social as responsable',
                            'movimientos.fecha',
                            'movimientos.tipo',
                            'productos.nombre as producto',
                            'item_movimientos.cantidad',
                            'item_movimientos.saldo'
                        )
                        ->orderBy('item_movimientos.productos_id', 'movimientos.fecha')
                        ->get();
            $titulo = 'KARDEX GENERAL';
            $reporte = 'kardex_general_fecha';

        }else if($reporte == 'kardex_general_producto'){

            $coleccion = DB::table('item_movimientos')
                        ->join('movimientos', 'item_movimientos.movimientos_id', '=', 'movimientos.id')
                        ->join('productos', 'item_movimientos.productos_id', '=', 'productos.id')
                        ->join('clientes', 'movimientos.clientes_id', '=', 'clientes.id')
                        ->where('productos.id', $producto)
                        ->select(
                            'movimientos.tipo_doc',
                            'movimientos.tipo_movimiento',
                            'movimientos.num_doc',
                            'clientes.razon_social as responsable',
                            'movimientos.fecha',
                            'movimientos.tipo',
                            'productos.nombre as producto',
                            'item_movimientos.cantidad',
                            'item_movimientos.saldo'
                        )
                        ->orderBy('item_movimientos.productos_id', 'movimientos.fecha')
                        ->get();
            $titulo = 'KARDEX GENERAL PRODUCTO';
            $reporte = 'kardex_general_producto';

        }else if($reporte == 'stock_productos'){
            $coleccion = DB::table('productos')
                        ->join('categorias', 'productos.categorias_id', '=', 'categorias.id')
                        ->join('unidades', 'productos.unidades_id', '=', 'unidades.id')
                        ->select(
                            'productos.codigo',
                            'productos.nombre',
                            'categorias.nombre as categoria',
                            'unidades.nombre as unidad',
                            'productos.stock',
                            'productos.precio_venta as precio'
                        )
                        ->get();
            $titulo = 'STOCK DE PRODUCTOS';
            $reporte = 'stock_productos';

        }else if($reporte == 'ingreso_material_fecha'){
            $coleccion = DB::table('item_movimientos')
                        ->join('movimientos', 'item_movimientos.movimientos_id', '=', 'movimientos.id')
                        ->join('productos', 'item_movimientos.productos_id', '=', 'productos.id')
                        ->join('clientes', 'movimientos.clientes_id', '=', 'clientes.id')
                        ->where('movimientos.fecha', '>=', $fecha_desde)
                        ->where('movimientos.fecha', '<=', $fecha_hasta)
                        ->whereRaw("movimientos.tipo='IN' or movimientos.tipo='DE' ")
                        ->select(
                            'movimientos.tipo_doc',
                            'movimientos.tipo_movimiento',
                            'movimientos.num_doc',
                            'clientes.razon_social as responsable',
                            //'clientes.nombre as responsable',
                            'movimientos.fecha',
                            'movimientos.tipo',
                            'productos.nombre as producto',
                            'item_movimientos.cantidad',
                            'item_movimientos.saldo'
                        )
                        ->orderBy('item_movimientos.productos_id', 'asc')
                        ->get();
            $titulo = 'INGRESO MATERIALES POR FECHA';
            $reporte = 'ingreso_material_fecha';
        }else if($reporte == 'ingreso_material_producto'){
            $coleccion = DB::table('item_movimientos')
                        ->join('movimientos', 'item_movimientos.movimientos_id', '=', 'movimientos.id')
                        ->join('productos', 'item_movimientos.productos_id', '=', 'productos.id')
                        ->join('clientes', 'movimientos.clientes_id', '=', 'clientes.id')
                        ->where('item_movimientos.productos_id', $producto)
                        ->whereRaw("movimientos.tipo='IN' or movimientos.tipo='DE' ")
                        ->select(
                            'movimientos.tipo_doc',
                            'movimientos.tipo_movimiento',
                            'movimientos.num_doc',
                            'clientes.razon_social as responsable',
                            //'clientes.nombre as responsable',
                            'movimientos.fecha',
                            'movimientos.tipo',
                            'productos.nombre as producto',
                            'item_movimientos.cantidad',
                            'item_movimientos.saldo'
                        )
                        ->orderBy('item_movimientos.productos_id', 'asc')
                        ->get();
            $titulo = 'INGRESO MATERIALES PRODUCTO';
            $reporte = 'ingreso_material_producto';
        }else if($reporte == 'salida_materiales_fecha'){
            $coleccion = DB::table('item_movimientos')
                        ->join('movimientos', 'item_movimientos.movimientos_id', '=', 'movimientos.id')
                        ->join('productos', 'item_movimientos.productos_id', '=', 'productos.id')
                        ->join('clientes', 'movimientos.clientes_id', '=', 'clientes.id')
                        ->where('movimientos.tipo', 'RE')
                        ->where('movimientos.fecha', '>=', $fecha_desde)
                        ->where('movimientos.fecha', '<=', $fecha_hasta)
                        ->select(
                            'movimientos.tipo_doc',
                            'movimientos.tipo_movimiento',
                            'movimientos.num_doc',
                            'clientes.razon_social as responsable',
                            //'concat(," ") as responsable',
                            'movimientos.fecha',
                            'movimientos.tipo',
                            'productos.nombre as producto',
                            'item_movimientos.cantidad',
                            'item_movimientos.saldo'
                        )
                        ->orderBy('item_movimientos.productos_id', 'asc')
                        ->get();
            $titulo = 'SALIDA MATERIALES POR FECHA';
            $reporte = 'salida_materiales_fecha';
        }else if($reporte == 'salida_materiales_producto'){
            $coleccion = DB::table('item_movimientos')
                        ->join('movimientos', 'item_movimientos.movimientos_id', '=', 'movimientos.id')
                        ->join('productos', 'item_movimientos.productos_id', '=', 'productos.id')
                        ->join('clientes', 'movimientos.clientes_id', '=', 'clientes.id')
                        ->where('item_movimientos.productos_id', $producto)
                        ->where('movimientos.tipo', 'RE')
                        ->select(
                            'movimientos.tipo_doc',
                            'movimientos.tipo_movimiento',
                            'movimientos.num_doc',
                            'clientes.razon_social as responsable',
                            'movimientos.fecha',
                            'movimientos.tipo',
                            'productos.nombre as producto',
                            'item_movimientos.cantidad',
                            'item_movimientos.saldo'
                        )
                        ->orderBy('item_movimientos.productos_id', 'asc')
                        ->get();
            $titulo = 'SALIDA MATERIALES PRODUCTO';
            $reporte = 'salida_materiales_producto';
        }else{
            return response()->json(array(
                'mensaje' => 0,
                'error' => 'no se identifico el parametro'
            ));
        }

        /*return response()->json(array(
            'coleccion' => $coleccion
        )); */

        return (new ReporteInventariosExport($reporte, $coleccion, $titulo))->download('data.xlsx');
    }
}
