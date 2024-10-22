<?php

namespace App\Http\Controllers\Api\web\dashboard;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use DB;
//REPORTE
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ReportesGeneralesExport;

class ReportesGenerales extends Controller
{
    public function ventasFecha(Request $request)
    {
        $fecha_ini=$request->fecha_inicio;
        $fecha_fin=$request->fecha_fin;

        $coleccion = DB::table('facturas')
                    ->join('clientes', 'facturas.clientes_id', '=', 'clientes.id')
                    ->whereDate('facturas.setFechaEmision','>=',$fecha_ini)
                    ->whereDate('facturas.setFechaEmision','<=',$fecha_fin)
                    ->select(
                        'facturas.id',
                        'clientes.razon_social as razon_social',
                        'facturas.setMtoImpVenta as total',
                        'facturas.setFechaEmision as fecha',
                        'facturas.estadoDesc as estado',
                        DB::raw("CONCAT_WS('',facturas.setSerie,'-', facturas.setCorrelativo) as documentos")
                    )
                ->orderBy('facturas.setFechaEmision')
                ->get();

        return response()->json(array(
            'data' => $coleccion
        ));
    }

    public function gastosFecha(Request $request)
    {
        $fecha_ini=$request->fecha_inicio;
        $fecha_fin=$request->fecha_fin;

        $coleccion = DB::table('gastos')
                    ->join('users', 'gastos.user_id', '=', 'users.id')
                    ->whereDate('gastos.fecha','>=',$fecha_ini)
                    ->whereDate('gastos.fecha','<=',$fecha_fin)
                    ->select(
                        'gastos.id',
                        'gastos.descripcion',
                        'gastos.monto',
                        'gastos.fecha',
                        'gastos.caja as caja',
                        'users.name as usuario'
                    )
                ->orderBy('gastos.fecha')
                ->get();

        return response()->json(array(
            'data' => $coleccion
        ));
    }

    public function cajasFecha(Request $request)
    {
        $fecha_ini=$request->fecha_inicio;
        $fecha_fin=$request->fecha_fin;

        $coleccion = DB::table('cajas')
                    ->whereDate('fecha','>=',$fecha_ini)
                    ->whereDate('fecha','<=',$fecha_fin)
                    ->where('tipomovimiento', 'CIERRE')
                    ->select(
                        'tipomovimiento as tipo_movimiento',
                        'descripcion',
                        DB::raw("CONCAT_WS('',fecha,' ', hora) as fecha"),
                        'monto'
                    )
                ->orderBy('cajas.fecha')
                ->get();

        return response()->json(array(
            'data' => $coleccion
        ));
    }

    public function boletas(Request $request)
    {
        $fecha_ini=$request->fecha_inicio;
        $fecha_fin=$request->fecha_fin;

        $coleccion = DB::table('facturas')
                    ->join('clientes', 'facturas.clientes_id', '=', 'clientes.id')
                    ->whereDate('facturas.setFechaEmision','>=',$fecha_ini)
                    ->whereDate('facturas.setFechaEmision','<=',$fecha_fin)
                    ->where('facturas.setTipoDoc', '03')
                    ->select(
                        'facturas.id',
                        'clientes.razon_social as razon_social',
                        'facturas.setMtoImpVenta as total',
                        'facturas.setFechaEmision as fecha',
                        'facturas.estadoDesc as estado',
                        DB::raw("CONCAT_WS('',facturas.setSerie,'-', facturas.setCorrelativo) as documentos")
                    )
                ->orderBy('facturas.setFechaEmision')
                ->get();

        return response()->json(array(
            'data' => $coleccion
        ));
    }

    public function facturas(Request $request)
    {
        $fecha_ini=$request->fecha_inicio;
        $fecha_fin=$request->fecha_fin;

        $coleccion = DB::table('facturas')
                    ->join('clientes', 'facturas.clientes_id', '=', 'clientes.id')
                    ->whereDate('facturas.setFechaEmision','>=',$fecha_ini)
                    ->whereDate('facturas.setFechaEmision','<=',$fecha_fin)
                    ->where('facturas.setTipoDoc', '01')
                    ->select(
                        'facturas.id',
                        'clientes.razon_social as razon_social',
                        'facturas.setMtoImpVenta as total',
                        'facturas.setFechaEmision as fecha',
                        'facturas.estadoDesc as estado',
                        DB::raw("CONCAT_WS('',facturas.setSerie,'-', facturas.setCorrelativo) as documentos")
                    )
                ->orderBy('facturas.setFechaEmision')
                ->get();

        return response()->json(array(
            'data' => $coleccion
        ));
    }

    public function notas(Request $request)
    {
        $fecha_ini=$request->fecha_inicio;
        $fecha_fin=$request->fecha_fin;

        $coleccion = DB::table('facturas')
                    ->join('clientes', 'facturas.clientes_id', '=', 'clientes.id')
                    ->whereDate('facturas.setFechaEmision','>=',$fecha_ini)
                    ->whereDate('facturas.setFechaEmision','<=',$fecha_fin)
                    ->where('facturas.setTipoDoc', '07')
                    ->select(
                        'facturas.id',
                        'clientes.razon_social as razon_social',
                        'facturas.setMtoImpVenta as total',
                        'facturas.setFechaEmision as fecha',
                        'facturas.estadoDesc as estado',
                        DB::raw("CONCAT_WS('',facturas.setSerie,'-', facturas.setCorrelativo) as documentos")
                    )
                ->orderBy('facturas.setFechaEmision')
                ->get();

        return response()->json(array(
            'data' => $coleccion
        ));
    }

    //excel
    //REPORTES
    public function reportes($reporte, $fecha_desde, $fecha_hasta){
        $coleccion = '';
        $titulo = '';
        //REPORTES BOLSA ASIGNADA POR FECHA
        if ($reporte == 'reporte_ventas'){

            $coleccion = DB::table('facturas')
                    ->join('clientes', 'facturas.clientes_id', '=', 'clientes.id')
                    ->whereDate('facturas.setFechaEmision','>=',$fecha_desde)
                    ->whereDate('facturas.setFechaEmision','<=',$fecha_hasta)
                    ->select(
                        'facturas.id',
                        'clientes.razon_social as razon_social',
                        'facturas.setMtoImpVenta as total',
                        'facturas.setFechaEmision as fecha',
                        'facturas.estadoDesc as estado',
                        DB::raw("CONCAT_WS('',facturas.setSerie,'-', facturas.setCorrelativo) as documentos")
                    )
                ->orderBy('facturas.setFechaEmision')
                ->get();
            $titulo = 'VENTAS';
            $reporte = 'reporte_ventas';

        }else if($reporte == 'reporte_gastos'){

            $coleccion = DB::table('gastos')
                    ->join('users', 'gastos.user_id', '=', 'users.id')
                    ->whereDate('gastos.fecha','>=',$fecha_desde)
                    ->whereDate('gastos.fecha','<=',$fecha_hasta)
                    ->select(
                        'gastos.id',
                        'gastos.descripcion',
                        'gastos.monto',
                        'gastos.fecha',
                        'gastos.caja as caja',
                        'users.name as usuario'
                    )
                ->orderBy('gastos.fecha')
                ->get();
            $titulo = 'GASTOS';
            $reporte = 'reporte_gastos';

        }else if($reporte == 'reporte_cajas'){

            $coleccion = DB::table('cajas')
                    ->whereDate('fecha','>=',$fecha_desde)
                    ->whereDate('fecha','<=',$fecha_hasta)
                    ->where('tipomovimiento', 'CIERRE')
                    ->select(
                        'tipomovimiento as tipo_movimiento',
                        'descripcion',
                        DB::raw("CONCAT_WS('',fecha,' ', hora) as fecha"),
                        'monto'
                    )
                ->orderBy('cajas.fecha')
                ->get();
            $titulo = 'CAJA CHICA';
            $reporte = 'reporte_cajas';
        
        }else if($reporte == 'reporte_boletas'){

            $coleccion = DB::table('facturas')
                    ->join('clientes', 'facturas.clientes_id', '=', 'clientes.id')
                    ->whereDate('facturas.setFechaEmision','>=',$fecha_desde)
                    ->whereDate('facturas.setFechaEmision','<=',$fecha_hasta)
                    ->where('facturas.setTipoDoc', '03')
                    ->select(
                        'facturas.id',
                        'clientes.razon_social as razon_social',
                        'facturas.setMtoImpVenta as total',
                        'facturas.setFechaEmision as fecha',
                        'facturas.estadoDesc as estado',
                        DB::raw("CONCAT_WS('',facturas.setSerie,'-', facturas.setCorrelativo) as documentos")
                    )
                ->orderBy('facturas.setFechaEmision')
                ->get();
            $titulo = 'BOLETAS';
            $reporte = 'reporte_boletas';

        }else if($reporte == 'reporte_facturas'){

            $coleccion = DB::table('facturas')
                    ->join('clientes', 'facturas.clientes_id', '=', 'clientes.id')
                    ->whereDate('facturas.setFechaEmision','>=',$fecha_desde)
                    ->whereDate('facturas.setFechaEmision','<=',$fecha_hasta)
                    ->where('facturas.setTipoDoc', '01')
                    ->select(
                        'facturas.id',
                        'clientes.razon_social as razon_social',
                        'facturas.setMtoImpVenta as total',
                        'facturas.setFechaEmision as fecha',
                        'facturas.estadoDesc as estado',
                        DB::raw("CONCAT_WS('',facturas.setSerie,'-', facturas.setCorrelativo) as documentos")
                    )
                ->orderBy('facturas.setFechaEmision')
                ->get();

            $titulo = 'FACTURAS';
            $reporte = 'reporte_facturas';

        }else if($reporte == 'reporte_notas'){

            $coleccion = DB::table('facturas')
                    ->join('clientes', 'facturas.clientes_id', '=', 'clientes.id')
                    ->whereDate('facturas.setFechaEmision','>=',$fecha_desde)
                    ->whereDate('facturas.setFechaEmision','<=',$fecha_hasta)
                    ->where('facturas.setTipoDoc', '07')
                    ->select(
                        'facturas.id',
                        'clientes.razon_social as razon_social',
                        'facturas.setMtoImpVenta as total',
                        'facturas.setFechaEmision as fecha',
                        'facturas.estadoDesc as estado',
                        DB::raw("CONCAT_WS('',facturas.setSerie,'-', facturas.setCorrelativo) as documentos")
                    )
                ->orderBy('facturas.setFechaEmision')
                ->get();

            $titulo = 'NOTAS DE CREDITO';
            $reporte = 'reporte_notas';


        }else{
            return response()->json(array(
                'mensaje' => 0,
                'error' => 'no se identifico el parametro'
            ));
        }

        /*return response()->json(array(
            'coleccion' => $coleccion
        )); */

        return (new ReportesGeneralesExport($reporte, $coleccion, $titulo))->download('data.xlsx');
    }
}
