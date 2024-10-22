<?php

namespace App\Http\Controllers\Api\web\dashboard;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use DB;
use App\Models\Productos;

class ResumenController extends Controller
{
    public function getProductos()
    {
        $productos = DB::table('items')
          ->join('productos', 'items.productos_id', '=', 'productos.id')
          ->select(
            'productos.nombre',
            DB::raw("COUNT(items.productos_id) AS conteo")
          )
          ->groupBy('items.productos_id')
          ->take(10)->get();

        return response()->json(array(
          'productos' => $productos
        ));
    }

    public function getDatosGenerales()
    {
      $ventas = DB::table('facturas')
                  ->whereRaw('(setTipoDoc=01 OR setTipoDoc=03) and (estado=1 OR estado=0)')
                  ->select(
                    DB::raw("SUM(setMtoImpVenta) as total")
                  )
                  ->first();

      $gastos = DB::table('gastos')
                  ->select(
                    DB::raw("SUM(monto) as total")
                  )
                ->first();

      return response()->json(array(
        'ventas' => $ventas,
        'gastos' => $gastos
      ));
    }

    public function getDatosGeneralesFecha(Request $request)
    {

      $mes = $request->mes;
      $anio = $request->anio;

      $ventas = DB::table('facturas')
                  ->whereMonth('setFechaEmision', $mes)
                  ->whereYear('setFechaEmision', $anio)
                  ->whereRaw('(setTipoDoc=01 OR setTipoDoc=03) and (estado=1 OR estado=0)')
                  ->select(
                    DB::raw("SUM(setMtoImpVenta) as total")
                  )
                  ->first();

      $gastos = DB::table('gastos')
                  ->whereMonth('fecha', $mes)
                  ->whereYear('fecha', $anio)
                  ->select(
                    DB::raw("SUM(monto) as total")
                  )
                ->first();

      return response()->json(array(
        'ventas' => $ventas,
        'gastos' => $gastos
      ));
    }
}
