<?php

namespace App\Http\Controllers\Api\web;
use DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Clientes;
use App\Models\Productos;

class CotizacionesController extends Controller{
    public function getCotizacion(Request $request){
        //return $request;

        $cliente = Clientes::find($request->clientes_id);
        
        $productos = $request->productos;
        $total = 0;
        $imponible = 0;
        $subtotal = 0;
        $impuestos = 0;
        foreach($productos as $item){
            $productos = Productos::find($item['id']);

            $dataProductos[] = [
                'impuesto' => $productos->impuestos_id,
                'nombre' => $productos->nombre,
                'id' => $productos->id,
                'codigo' => $productos->codigo,
                'cantidad' => $item['cantidad'],
                'precio_unitario' => $productos->impuestos_id == 1 ? ($item['precio_unitario'] / 1.18) : $item['precio_unitario'],
                'precio_total' => $productos->impuestos_id == 1 ? (($item['precio_unitario'] / 1.18)) * $item['cantidad'] : $item['precio_total']
            ];

            if($productos->impuestos_id == 1){
                $imponible =( (($item['precio_unitario'] / 1.18)) * $item['cantidad']) + $imponible;
                $subtotal = ( (($item['precio_unitario'] / 1.18)) * $item['cantidad']) + $subtotal;
            }else{
                $subtotal = $item['precio_total'] + $subtotal;
            }

            

            //$total = $total + $item['precio_total'];
        };

        $impuestos = $imponible * 0.18;
        $total = $subtotal + $impuestos;
        //return $dataProductos;
        $pdf = Pdf::loadView('exports/cotizaciones-pdf', ['data' => $request, 'cliente' => $cliente,  'productos' => $dataProductos, 'total' => $total, 'impuestos' => $impuestos, 'imponible' => $imponible, 'subtotal' => $subtotal]);
        return $pdf->download('invoice.pdf');
        //return view('exports/cotizaciones-pdf', $data = ['data' => $request, 'cliente' => $cliente, 'total' => $total]);
    }

}