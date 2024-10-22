<?php

namespace App\Http\Controllers\Api\web\inventarios;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use DB;
use Auth;

use App\Models\Clientes;
use App\Models\Productos;
use App\Models\Movimientos;
use App\Models\ItemMovimientos;

use App\Transformers\IngresosTransformer;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;

class IngresosController extends Controller
{
    private $fractal;
    private $ingresosTransformer;

    function __construct(Manager $fractal, IngresosTransformer $ingresosTransformer){
        $this->fractal = $fractal;
        $this->ingresosTransformer = $ingresosTransformer;
    }

    public function index(Request $request){
        //filtros
        $order='DESC'; if ($request->_order) { $order = $request->_order;  }
        $row='id'; if ($request->_sort) { $row = $request->_sort; }
        $id= '';                if ($request->id_like) { $id = $request->id_like; }
        $tipo_doc= '';          if ($request->tipo_doc_like) { $tipo_doc = $request->tipo_doc_like; }
        $num_doc= '' ;          if ($request->num_doc_like) { $num_doc = $request->num_doc_like; }
        $tipo_movimineto= '' ;  if ($request->tipo_movimineto_like) { $tipo_movimineto = $request->tipo_movimineto_like; }
        $proveedor= '' ;        if ($request->proveedor_like) { $proveedor = $request->proveedor_like; }
        $fecha = '';            if ($request->fecha_like) { $fecha = $request->fecha_like; }

        $ingresosPaginator = Movimientos::where('id','like', '%' . $id . '%')
                                        ->where('tipo_doc','like', '%' . $tipo_doc. '%')
                                        ->where('num_doc','like', '%' .$num_doc . '%')
                                        ->where('tipo_movimiento','like', '%' .$tipo_movimineto . '%')
                                        //->where('fecha','like', '%' . $fecha . '%')
                                        ->where('tipo','IN')
                                        ->whereHas('clientes',function($q)use($proveedor){
                                        $q->where('razon_social','like', '%' .$proveedor. '%');
                                        })
                                        ->orderby($row,$order)
                                        ->paginate(10);

        $movimientos =  new Collection($ingresosPaginator->items(), $this->ingresosTransformer);
        $movimientos->setPaginator(new IlluminatePaginatorAdapter($ingresosPaginator));

        $movimientos = $this->fractal->createData($movimientos);
        return $movimientos->toArray();
    }

    public function store(Request $request){
        $movimiento = new Movimientos();
        $movimiento->fill($request->all());
        $movimiento->tipo_movimiento = 'COMPRA';
        $movimiento->tipo='IN';
        if($movimiento->save()){
          $movimiento_id=$movimiento->id;  //<---
          foreach ($request['productos'] as $producto){
            $producto_id=$producto['id'];  //<---
            $cantidad=$producto['cantidad'];//<---
            $Eprecio=explode("S/. ",$producto['precio_unitario']);
            $precio=$Eprecio[1];//<---

            //OBTENER SALDO
            $saldo=$cantidad; //<---
            $precio=$precio;
            $tipo='IN'; //<--
            $items=ItemMovimientos::PorProducto($producto_id)->get();
            $cant=count($items);
            foreach($items as $item){
              $precio+=$item->precio;
              if($item->tipo=='IN'){
                $saldo+=$item->cantidad;
              }else{
                $saldo-=$item->cantidad;
              }
            }
            //CREACION DE ITEMS
            $itenMovimiento=new ItemMovimientos();
            $itenMovimiento->movimientos_id=$movimiento_id;
            $itenMovimiento->productos_id=$producto_id;
            $itenMovimiento->cantidad=$cantidad;
            $itenMovimiento->saldo=$saldo;
            $itenMovimiento->precio=$Eprecio[1];
            $itenMovimiento->tipo=$tipo;
            if($itenMovimiento->save()){
              $producto=Productos::find($producto_id);
              $producto->stock=$saldo;
              $producto->precio_compra=$precio/($cant+1);
              $producto->save();
            }
          }
          return response()->json(array(
              'mensaje' => 1,
          ));
        }
    }


    public function show($ingreso_id){
        $movimiento = Movimientos::find($ingreso_id);
        $cliente= $movimiento->clientes->razon_social;
        return response()->json(array(
            'movimiento' => $movimiento,
            'cliente' => $cliente,
        ));
    }

    public function destroy($movimiento_id){
        $movimiento = Movimientos::find($movimiento_id);
        $items=ItemMovimientos::PorMovimiento($movimiento_id)->get();
        foreach($items as $item){
          $id=$item->id;
          $productos_id=$item->productos_id;
          $cantidad=$item->scantidad;
          if($item->delete()){
            $itemProds=ItemMovimientos::PorProducto($productos_id)->get();
            $saldo=0;
            $precio=0;
            $registros=count($itemProds);
            if($registros==0){$registros=1;}
            foreach($itemProds as $itemProd){
              $precio+=$itemProd->precio;
              if($itemProd->tipo=='IN'){ $saldo+=$itemProd->cantidad; }else{ $saldo-=$itemProd->cantidad; }
              $itemMov=ItemMovimientos::find($itemProd->id);
              $itemMov->saldo=$saldo;
              $itemMov->save();
            }
            $producto=Productos::find($productos_id);
            $producto->stock=$saldo;
            $producto->precio_compra=$precio/$registros;
            $producto->save();
          }
        }
        if($movimiento->delete()){
            return response()->json(array(
                'mensaje' => 1
            ));
        }else{
            return response()->json(array(
                'mensaje' => 2
            ));
        }
    }

    //obtener item_movimiento del movimineto
    public function obtenerTablaDetalle($movimiento_id){
        $items=ItemMovimientos::PorMovimiento($movimiento_id)->get();
        $data = [];
        $subtotal=0;
        $igv=0;
        $total=0;
        foreach ($items as $item){
          $data [] = [
            'id' => $item->productos->codigo,
            'descripcion' =>  $item->productos->nombre,
            'cantidad' => $item->cantidad,
            'unidad' => $item->productos->unidades->simbolo,
            'impuesto' => number_format($item->productos->impuestos->tasa,2).' - '.$item->productos->impuestos->nombre,
            'precio_unitario' => 'S/. '.$item->precio,
            'precio_total' => 'S/. '.number_format($item->cantidad*$item->precio,2),
            'precio_final' => number_format($item->cantidad*$item->precio,2),
          ];
          $precio=$item->cantidad*$item->precio;
          $tasa=$item->productos->impuestos->tasa;
          if($tasa!=0){
            $subIgv=$precio-($precio/(1.18));
          }else{
            $subIgv=0;
          }

          $sub=$precio-$subIgv;

          $subtotal+=$sub;
          $igv+=$subIgv;
          $total+=$item->cantidad*$item->precio;
        }
        return response()->json(array(
            'items' => $data,
            'subtotal' => 'S/. '.number_format($subtotal,2),
            'igv' => 'S/. '.number_format($igv,2),
            'total' => 'S/. '.number_format($total,2),

        ));
    }

    public function recalcularSaldo(){
      $productos=Productos::all();
      foreach($productos as $producto){
        $producto_id=$producto->id;
        //OBTENER SALDO
        $saldo=0;
        $items=ItemMovimientos::PorProducto($producto_id)->get();
        $cant=count($items);
        foreach($items as $item){
          if($item->tipo=='IN'){
            $saldo+=$item->cantidad;
          }elseif($item->tipo=='DE'){
            $saldo+=$item->cantidad;
          }elseif($item->tipo=='RE'){ //devolucion
            $saldo-=$item->cantidad;
          }
          else{
            $saldo-=$item->cantidad;
          }
          $itemMov=ItemMovimientos::find($item->id);
          $itemMov->saldo=$saldo;
          $itemMov->save();
        }
        $producto=Productos::find($producto_id);
        $producto->stock=$saldo;
        $producto->save();
      }
    }
    //DATOS AUXILAIRES
    public function dataAuxiliarIngreso(){
        $clientes = DB::table('clientes')
                    ->where('tipo_cliente', 'PROVEEDOR')
                    ->orWhere('tipo_cliente', 'CLIENTE/PROVEEDOR')
                    ->select(
                        'id',
                        'razon_social'
                    )
                    ->get();

        $productos = DB::table('productos')
                    //->where()
                    ->select(
                       'id',
                       DB::raw("CONCAT_WS('',codigo,' | ', nombre, ' | STOCK: ', stock, ' | PRECIO: S/.', precio_venta) as descripcion")
                    )
                    ->get();
        return response()->json(array(
            'clientes' => $clientes,
            'productos' => $productos,
        ));
    }

    public function dataAuxiliarCotizacion(){
      $clientes = DB::table('clientes')
                  ->where('tipo_cliente', 'PROVEEDOR')
                  ->orWhere('tipo_cliente', 'CLIENTE/PROVEEDOR')
                  ->select(
                      'id',
                      'razon_social'
                  )
                  ->get();

      $productos = DB::table('productos')
                  //->where()
                  ->select(
                     'id',
                     DB::raw("CONCAT_WS('',codigo,' | ', nombre) as descripcion"),
                     'precio_venta'
                  )
                  ->get();

                  
      return response()->json(array(
          'clientes' => $clientes,
          'productos' => $productos,
      ));
  }

    //actualizar productos
    public function updateProductos()
    {
        $productos = DB::table('productos')
                    //->where()
                    ->select(
                       'id',
                       DB::raw("CONCAT_WS('',codigo,' | ', nombre, ' | STOCK: ', stock, ' | PRECIO: S/.', precio_venta) as descripcion")
                    )
                    ->get();

        return response()->json(array(
            'productos' => $productos,
        ));
    }
}
