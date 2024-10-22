<?php

namespace App\Http\Controllers\Api\web\caja;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


use DB;
use Auth;
use App\Models\User;
use App\Models\Gastos;

use App\Transformers\GastosTransformer;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use App\Models\Cajas;

class GastosController extends Controller
{

    private $fractal;
    private $gastosTransformer;

    function __construct(Manager $fractal, GastosTransformer $gastosTransformer)
    {
        $this->fractal = $fractal;
        $this->gastosTransformer = $gastosTransformer;
    }

    public function index(Request $request)
    {
        $order='ASC';
        if ($request->_order) {
            $order = $request->_order;
        }
        $row='id';
        if ($request->_sort) {
            $row = $request->_sort;
        }
        //filtros
        $descripcion = '';
        if ($request->descripcion_like) {
            $descripcion = $request->descripcion_like;
        }

        //paginacion
        $gastosPaginator = Gastos::where('descripcion', 'like', '%' . $descripcion . '%')
                        ->orderby($row,$order)
                        ->paginate(10);

        $gastos =  new Collection($gastosPaginator->items(), $this->gastosTransformer);
        $gastos->setPaginator(new IlluminatePaginatorAdapter($gastosPaginator));

        $gastos = $this->fractal->createData($gastos);
        return $gastos->toArray();
    }


    public function store(Request $request)
    {
        $carbon = new \Carbon\Carbon();
        $time = $carbon->toTimeString();

        $caja = $request->caja;
        if ($caja == true) {
            $caja = 1;
        }else if($caja == false){
            $caja = 0;
        }

        $gasto = new Gastos();
        $gasto->fill($request->all());
        $gasto->user_id = Auth::user()->id;
        if($gasto->save()){

            if ($caja == 1) {
                //guardar movimiento
                $caja = new Cajas();
                $caja->tipomovimiento = 'EGRESO';
                $caja->nombres = $request->razon_social;
                $caja->descripcion = $request->descripcion;
                $caja->fecha = $request->fecha;
                $caja->hora = $time;
                $caja->tipo_pago = 'EFECTIVO';
                $caja->monto = $request->monto;
                $caja->cierre = 'NO';
                $caja->user_id = Auth::user()->id;
                $caja->gastos_id = $gasto->id;
                if ($caja->save()) {
                    return response()->json(array(
                        'mensaje' => 1,
                    ));
                }
            }else {
                return response()->json(array(
                    'mensaje' => 1,
                ));
            }         
        }else{
            return response()->json(array(
                'mensaje' => 2
            ));
        }
    }


    public function show($id)
    {
        $gasto = Gastos::find($id);
        $caja = Cajas::where('gastos_id',$gasto->id)->first();
        
        return response()->json(array(
            'gasto' => $gasto,
            'caja' => $caja
        ));
    }


    public function update(Request $request, $id)
    {
        $carbon = new \Carbon\Carbon();
        $time = $carbon->toTimeString();

        $caja = $request->caja;
        if ($caja == true) {
            $caja = 1;
        }else if($caja == false){
            $caja = 0;
        }

        $gasto = Gastos::find($id);

        if ($caja == 1) {
            $caja_data = Cajas::where('gastos_id', $gasto->id)->first();
            if ($caja_data != null) {
                $caja_data->tipomovimiento = 'EGRESO';
                $caja_data->nombres = $request->razon_social;
                $caja_data->descripcion = $request->descripcion;
                $caja_data->fecha = $request->fecha;
                $caja_data->hora = $time;
                $caja_data->tipo_pago = 'EFECTIVO';
                $caja_data->monto = $request->monto;
                $caja_data->cierre = 'NO';
                $caja_data->user_id = Auth::user()->id;
                $caja_data->gastos_id = $gasto->id;
            }else {
                //guardar movimiento
                $caja_data = new Cajas();
                $caja_data->tipomovimiento = 'EGRESO';
                $caja_data->nombres = $request->razon_social;
                $caja_data->descripcion = $request->descripcion;
                $caja_data->fecha = $request->fecha;
                $caja_data->hora = $time;
                $caja_data->tipo_pago = 'EFECTIVO';
                $caja_data->monto = $request->monto;
                $caja_data->cierre = 'NO';
                $caja_data->user_id = Auth::user()->id;
                $caja_data->gastos_id = $gasto->id;
            }
            $caja_data->save();
        }else {
            $caja_data = Cajas::where('gastos_id', $gasto->id)->first();
            if ($caja_data != null) {
                //eliminamos
                $caja_data->delete();
            }
        }

        $gasto->fill($request->all());
        $gasto->user_id = Auth::user()->id;
        if($gasto->save()){
            return response()->json(array(
                'mensaje' => 1
            ));
        }else{
            return response()->json(array(
                'mensaje' => 2
            ));
        }
    }


    public function destroy($id)
    {
        $gasto = Gastos::find($id);
        //verificamos si tiene una caja
        $caja = Cajas::where('gastos_id', $gasto->id)->first();
        if ($caja != null) {
            if ($caja->delete()) {
                if ($gasto->delete()) {
                    return response()->json(array(
                        'mensaje' => 1
                    ));
                }
            }
        }else {
            if ($gasto->delete()) {
                return response()->json(array(
                    'mensaje' => 1
                ));
            }
        }
    }
}
