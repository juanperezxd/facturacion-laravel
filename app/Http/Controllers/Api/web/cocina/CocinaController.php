<?php

namespace App\Http\Controllers\Api\web\cocina;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use DB;
use Auth;
use App\Models\ItemsVentas;
//events pusher
use App\Events\ProductoListo;

class CocinaController extends Controller
{
    //obtener mesas ocupadas
    public function getMesasCocina()
    {
        $ventas = DB::table('ventas')
                ->join('users', 'ventas.user_id', '=', 'users.id')
                ->join('mesas', 'ventas.mesas_id', '=', 'mesas.id')
                ->join('escenarios', 'mesas.escenarios_id', '=', 'escenarios.id')
                ->select(
                    'ventas.id',
                    'ventas.fecha_pago',
                    'ventas.valor_total',
                    'ventas.user_id',
                    'ventas.created_at',
                    'users.name as usuario',
                    'mesas.nombre as mesa',
                    'escenarios.nombre as escenario'
                )
                ->orderBy('ventas.created_at')
                ->get();

        //RECORRER LOS ITEM DE LAS VENTAS
        $data = [];

        foreach ($ventas as $venta) {
            //recorremos los item de las ventas
            $items_ventas = ItemsVentas::select('preparado')->get();
            $count = 0;
            foreach ($items_ventas as $item) {
                if ($item->preparado == 0) {
                    $count = $count + 1;
                }
            }

            if ($count != 0) {
                $data [] = [
                    'id' => $venta->id,
                    'fecha_pago' => $venta->fecha_pago,
                    'valor_total' => $venta->valor_total,
                    'user_id' => $venta->user_id,
                    'created_at' => $venta->created_at,
                    'usuario' => $venta->usuario,
                    'mesa' => $venta->mesa,
                    'escenario' => $venta->escenario,
                    'productos' => $count
                ];
            }
        }


        return response()->json(array(
            'mesas' => $data
        ));
    }

    public function getMesaDetalle($idVenta)
    {
        $productos = DB::table('items_ventas')
        ->where('ventas_id', $idVenta)
        ->get();

        return response()->json(array(
            'productos' => $productos
        ));
    }

    public function getMesaDetalleListo(Request $request)
    {

       $idMozo = $request->idMozo;
       $idItem = $request->idItem;
       $mesa = $request->mesa;
       $producto = $request->producto;

       $item = ItemsVentas::find($idItem);
       $item->preparado = 1;
       if ($item->save()) {

            //event pusher
            event(new ProductoListo(1, $idMozo, $mesa, $producto));

           return response()->json(array(
            'mensaje' => 1,
           ));
       }
    }
}
