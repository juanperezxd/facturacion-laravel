<?php

namespace App\Http\Controllers\Api\web\dashboard;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use DB;
//EXPORTAR EXCEL
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ContableTotalExport;
use App\Exports\ContableDetalladoExport;
use App\Models\Facturas;
use App\Exports\ContableCompendioBExport;
use App\Exports\ContableCompendioFExport;

class ReportesContables extends Controller
{
    public function resumenTotales($mes, $anio, $nombre_mes)
    {
        $boletas = DB::table('facturas')
                ->whereMonth('setFechaEmision', $mes)
                ->whereYear('setFechaEmision', $anio)
                ->where('setTipoDoc', '03')
                ->select(
                    'id',
                    'cliente_setNumDoc',
                    'cliente_setRznSocial',
                    'setMtoImpVenta',
                    'setFechaEmision',
                    'estadoDesc',
                    DB::raw("CONCAT_WS('',setSerie,'-', setCorrelativo) as documento")
                )
                ->orderBy('setFechaEmision')
                ->get();

        $facturas = DB::table('facturas')
            ->whereMonth('setFechaEmision', $mes)
            ->whereYear('setFechaEmision', $anio)
            ->where('setTipoDoc', '01')
            ->select(
                'id',
                'cliente_setNumDoc',
                'cliente_setRznSocial',
                'setMtoImpVenta',
                'setFechaEmision',
                'estadoDesc',
                DB::raw("CONCAT_WS('',setSerie,'-', setCorrelativo) as documento")
            )
            ->orderBy('setFechaEmision')
            ->get();

        $notas = DB::table('facturas')
        ->whereMonth('setFechaEmision', $mes)
        ->whereYear('setFechaEmision', $anio)
        ->where('setTipoDoc', '07')
        ->select(
            'id',
            'cliente_setNumDoc',
            'cliente_setRznSocial',
            'setMtoImpVenta',
            'setFechaEmision',
            'estadoDesc',
            DB::raw("CONCAT_WS('',setSerie,'-', setCorrelativo) as documento")
        )
        ->orderBy('setFechaEmision')
        ->get();

        return (new ContableTotalExport($boletas, $facturas, $notas, $nombre_mes, $anio))->download('data.xlsx');
    }

    public function resumenDetallado($mes, $anio, $nombre_mes)
    {
        
        $facturas = Facturas::where('setTipoDoc', '01')
                    ->whereMonth('setFechaEmision', $mes)
                    ->whereYear('setFechaEmision', $anio)
                    ->orderBy('setFechaEmision')
                    ->get();

        $fecha = $nombre_mes.' '.$anio;

        return (new ContableDetalladoExport($facturas,$fecha))->download('data.xlsx');
    }

    public function conpendioBoletas($mes, $anio, $nombre_mes)
    {
        $boletas = DB::table('facturas')
                ->whereMonth('setFechaEmision', $mes)
                ->whereYear('setFechaEmision', $anio)
                ->where('setTipoDoc', '03')
                ->select(
                    'id',
                    'setFechaEmision',
                    'setSerie',
                    'setCorrelativo',
                    'cliente_setRznSocial',
                    'cliente_setNumDoc',
                    'setMtoOperGravadas',
                    'setMtoIGV',
                    'setMtoImpVenta',
                    'estadoDesc'
                )
                ->orderBy('setFechaEmision')
                ->get();

        $notas = DB::table('facturas')
                ->whereMonth('setFechaEmision', $mes)
                ->whereYear('setFechaEmision', $anio)
                ->where('setTipoDoc', '07')
                ->where('setSerie', 'like', 'B%')
                ->select(
                    'id',
                    'setFechaEmision',
                    'setSerie',
                    'setCorrelativo',
                    'cliente_setRznSocial',
                    'cliente_setNumDoc',
                    'setMtoOperGravadas',
                    'setMtoIGV',
                    'setMtoImpVenta',
                    'estadoDesc'
                )
                ->orderBy('setFechaEmision')
                ->get();


        $fecha = $nombre_mes.' '.$anio;

        return (new ContableCompendioBExport($boletas,$notas, $fecha))->download('data.xlsx');
    }

    public function conpendioFacturas($mes, $anio, $nombre_mes)
    {
        $facturas = DB::table('facturas')
            ->whereMonth('setFechaEmision', $mes)
            ->whereYear('setFechaEmision', $anio)
            ->where('setTipoDoc', '01')
            ->where('setSerie', 'like', 'F%')
            ->select(
                'id',
                'setFechaEmision',
                'setSerie',
                'setCorrelativo',
                'cliente_setRznSocial',
                'cliente_setNumDoc',
                'setMtoOperGravadas',
                'setMtoIGV',
                'setMtoImpVenta',
                'estadoDesc'
            )
            ->orderBy('setFechaEmision')
            ->get();

        $notas = DB::table('facturas')
                ->whereMonth('setFechaEmision', $mes)
                ->whereYear('setFechaEmision', $anio)
                ->where('setTipoDoc', '07')
                ->select(
                    'id',
                    'setFechaEmision',
                    'setSerie',
                    'setCorrelativo',
                    'cliente_setRznSocial',
                    'cliente_setNumDoc',
                    'setMtoOperGravadas',
                    'setMtoIGV',
                    'setMtoImpVenta',
                    'estadoDesc'
                )
                ->orderBy('setFechaEmision')
                ->get();

        $fecha = $nombre_mes.' '.$anio;
        return (new ContableCompendioFExport($facturas, $notas ,$fecha))->download('data.xlsx');
    }
}
