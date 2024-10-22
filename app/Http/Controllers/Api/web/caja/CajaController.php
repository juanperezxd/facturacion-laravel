<?php

namespace App\Http\Controllers\Api\web\caja;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use DB;
use Auth;
use App\Models\User;

use App\Models\Cajas;
use App\Models\Gastos;

class CajaController extends Controller
{
    
    public function index()
    {
        $cajas = Cajas::where('cierre', 'NO')->get();
        $saldo = Cajas::where('tipomovimiento', 'SALDO')->orderBy('updated_at', 'desc')->first();
        //saldo
        if (count($cajas) > 0) {
            return response()->json(array(
                'cajas' => $cajas,
                'saldo' => $saldo,
                'apertura' => 1
            ));
        }else {
            return response()->json(array(
                'cajas' => $cajas,
                'saldo' => $saldo,
                'apertura' => 0
            ));
        }
    }

    public function store(Request $request)
    {
        $carbon = new \Carbon\Carbon();
        $time = $carbon->toTimeString();
        $date = $carbon::now();
        $hoy=$date->format('Y-m-d');

        //guardar movimiento
        $caja = new Cajas();
        $caja->tipomovimiento = $request->tipomovimiento;
        $caja->descripcion = $request->descripcion;
        $caja->fecha = $hoy;
        $caja->hora = $time;
        $caja->tipo_pago = 'EFECTIVO';
        $caja->monto = $request->monto;
        $caja->cierre = 'NO';
        $caja->user_id = Auth::user()->id;
        if ($caja->save()) {
            return response()->json(array(
                'mensaje' => 1,
            ));
        }else {
            return response()->json(array(
                'mensaje' => 2,
            ));
        }           

    }

    public function show($id)
    {
        $caja = Cajas::find($id);
        return response()->json($caja);
    }


    public function update(Request $request, $id)
    {
        $carbon = new \Carbon\Carbon();
        $time = $carbon->toTimeString();
        $date = $carbon::now();
        $hoy=$date->format('Y-m-d');

        //guardar movimiento
        $caja = Cajas::find($id);
        $caja->tipomovimiento = $request->tipomovimiento;
        $caja->descripcion = $request->descripcion;
        $caja->fecha = $hoy;
        $caja->hora = $time;
        $caja->tipo_pago = 'EFECTIVO';
        $caja->monto = $request->monto;
        $caja->cierre = 'NO';
        $caja->user_id = Auth::user()->id;
        if ($caja->save()) {
            return response()->json(array(
                'mensaje' => 1,
            ));
        }else {
            return response()->json(array(
                'mensaje' => 2,
            ));
        } 
    }

    public function destroy($id)
    {
        $caja = Cajas::find($id);
        if ($caja->delete()) {
            return response()->json(array(
                'mensaje' => 1,
            ));
        }
    }

    public function dataCajas()
    {
        $cajas = DB::table('cajas')
                ->select('id')
                ->orderBy('updated_at')
                ->get();

        return response()->json(array(
            'cajas' => $cajas
        ));
    }

    //FUNCIONES AUXILAIRES

    //cerrar caja
    public function cerrarCaja(Request $request)
    {
        $carbon = new \Carbon\Carbon();
        $time = $carbon->toTimeString();
        $date = $carbon::now();
        $hoy=$date->format('Y-m-d');

        //DATOS DEL REQUEST
        $monto = $request->monto;
        $retiro = $request->retiro;
        $monto_real = $request->monto_real;

        //creamos el registro de cierre;
        $cierre = new Cajas();
        $cierre->tipomovimiento = 'CIERRE';
        $cierre->descripcion = 'CIERRE';
        $cierre->fecha = $hoy;
        $cierre->hora = $time;
        $cierre->tipo_pago = 'EFECTIVO';
        $cierre->monto = $monto;
        $cierre->cierre = 'NO';
        $cierre->user_id = Auth::user()->id;
        if ($cierre->save()) {
            //creamos el registro de saldo
            $saldo = new Cajas();
            $saldo->tipomovimiento = 'SALDO';
            $saldo->descripcion = 'SALDO';
            $saldo->fecha = $hoy;
            $saldo->hora = $time;
            $saldo->tipo_pago = 'EFECTIVO';
            $saldo->monto = $monto_real;
            $saldo->cierre = 'NO';
            $saldo->user_id = Auth::user()->id;
            if ($saldo->save()) {
                //cerramos la caja
                Cajas::where('cierre','NO')->update(['cierre' => 'SI']);
                return response()->json(array(
                    'mensaje' => 1
                ));
            }
        } 
    }

    //apeprturar caja
    public function aperturarCaja(Request $request)
    {
        $carbon = new \Carbon\Carbon();
        $time = $carbon->toTimeString();
        $date = $carbon::now();
        $hoy=$date->format('Y-m-d');

        $monto = $request->monto;

        $apertura = new Cajas();
        $apertura->tipomovimiento = 'APERTURA';
        $apertura->descripcion = 'APERTURA';
        $apertura->fecha = $hoy;
        $apertura->hora = $time;
        $apertura->tipo_pago = 'EFECTIVO';
        $apertura->monto = $monto;
        $apertura->cierre = 'NO';
        $apertura->user_id = Auth::user()->id;
        if ($apertura->save()) {
            return response()->json(array(
                'mensaje' => 1
            ));
        }
    }

    //ultimos cierres
    public function getCierresCaja()
    {
        $cajas = DB::table('cajas')
                ->join('users', 'cajas.user_id', '=', 'users.id')
                ->where('cajas.cierre', 'SI')
                ->select(
                    'cajas.id',
                    'cajas.updated_at',
                    'users.name as usuario'
                )
                ->groupBy('updated_at')
                ->get();

        return response()->json(array(
            'cajas' => $cajas
        ));
    }

    public function getDataCierre($fecha)
    {
        $movimientos = DB::table('cajas')
                ->join('users', 'cajas.user_id', '=', 'users.id')
                ->where('cajas.updated_at', $fecha)
                ->select(
                    'cajas.id',
                    'cajas.updated_at',
                    'cajas.tipomovimiento',
                    'cajas.descripcion',
                    'cajas.monto',
                    'users.name as usuario'

                )
                ->get();

        return response()->json(array(
            'movimientos' => $movimientos
        ));
    }

    //verificar si la caja esta abierta (modulo ventas)
    public function cajaAbierta()
    {
        $caja = Cajas::select('id')->where('cierre', 'NO')->first();

        return response()->json(array(
            'caja' => $caja
        ));
    }

    //busqueda cierres por fecha
    public function busquedaCierre(Request $request)
    {
        $fecha = $request->fecha;

        $cajas = DB::table('cajas')
                ->join('users', 'cajas.user_id', '=', 'users.id')
                ->where('cajas.cierre', 'SI')
                ->whereDate('cajas.updated_at', $fecha)
                ->select(
                    'cajas.id',
                    'cajas.updated_at',
                    'users.name as usuario'
                )
                ->groupBy('updated_at')
                ->get();

        return response()->json(array(
            'cajas' => $cajas,
        ));
    }
}
