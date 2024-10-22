<?php

namespace App\Http\Controllers\Api\web\ventas;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Auth;
use App\Models\User;
use App\Models\Escenarios;
use App\Models\Mesas;
use DB;
use App\Models\Ventas;
use App\Models\ItemsVentas;

class MesasController extends Controller
{
    
    public function index()
    {
        //
    }

    public function store(Request $request)
    {
        $mesas = new Mesas();
        $mesas->fill($request->all());
        $mesas->created_for = Auth::user()->id;
        $mesas->updated_for = Auth::user()->id;
        if($mesas->save()){
            return response()->json(array(
                'mensaje' => 1,
                'escenario' => $mesas->escenarios_id,
            ));
        }else{
            return response()->json(array(
                'mensaje' => 2
            ));
        }
    }

    public function show($id)
    {
        $mesa = Mesas::find($id);
        $data = [];
        if ($mesa != null) {
            $create_for = User::find($mesa->created_for);
            $update_for = User::find($mesa->created_for);

            $data = [
                'id' => $mesa->id,
                'nombre' => $mesa->nombre,
                'escenarios_id' => $mesa->escenarios_id,
                'estado' => $mesa->estado,
                'created_for' => $create_for->name,
                'update_for' => $update_for->name,
            ];
        }

        return response()->json($data);
    }

    public function update(Request $request, $id)
    {
        $mesa = Mesas::find($id);
        $mesa->fill($request->all());
        $mesa->updated_for = Auth::user()->id;
        if($mesa->save()){
            return response()->json(array(
                'mensaje' => 1,
                'escenario' => $mesa->escenarios_id,
            ));
        }else{
            return response()->json(array(
                'mensaje' => 2
            ));
        }
    }

    //eliminar mesa
    public function destroy($id)
    {
        $mesa = Mesas::find($id);
        if($mesa->delete()){
            return response()->json(array(
                'mensaje' => 1
            ));
        }else{
            return response()->json(array(
                'mensaje' => 2
            ));
        }
    }

    //funciones auxialires
    //obtener escenarios y mesas
    public function getEscenarios1($idEscenario)
    {
        if ($idEscenario != 0) {
            
            $escenarios = Escenarios::select('id', 'nombre')->get();
            $escenario1 = Escenarios::select('id', 'nombre', 'estado','created_for','updated_for')->where('id', $idEscenario)->first();

            if ($escenario1 != null) {
                $mesas = Mesas::select('id','nombre','escenarios_id','estado','relacion')->where('escenarios_id', $escenario1->id)->get();
                
                $data = [];

                foreach ($mesas as $mesa) {

                    $venta = DB::table('ventas')
                        ->join('clientes', 'ventas.clientes_id', '=', 'clientes.id')
                        ->join('users', 'ventas.user_id', '=', 'users.id')
                        ->where('ventas.mesas_id', $mesa->id)
                        ->select(
                            'ventas.fecha_pago',
                            'ventas.valor_total',
                            'ventas.created_at',
                            'clientes.razon_social as cliente',
                            'clientes.id as cliente_id',
                            'users.name as usuario'
                        )
                        ->first();

                    if ($venta != null) {
                        $data[] = [
                            'id' => $mesa->id,
                            'nombre' => $mesa->nombre,
                            'escenarios_id' => $mesa->escenarios_id,
                            'estado' => $mesa->estado,
                            'venta' => 1,
                            'fecha_pago' => $venta->fecha_pago,
                            'valor_total' => (float)$venta->valor_total,
                            'cliente' => $venta->cliente,
                            'cliente_id' => $venta->cliente_id,
                            'relacion' => $mesa->relacion,
                            'mesa_asociada' => null,
                            'usuario' => $venta->usuario,
                            'created_at' => $venta->created_at
                        ];
                    }else {
                        $data[] = [
                            'id' => $mesa->id,
                            'nombre' => $mesa->nombre,
                            'escenarios_id' => $mesa->escenarios_id,
                            'estado' => $mesa->estado,
                            'venta' => 0,
                            'fecha_pago' => '2100-05-30 12:12:00',
                            'valor_total' => '',
                            'cliente' => '',
                            'cliente_id' => '',
                            'relacion' => $mesa->relacion,
                            'mesa_asociada' => null,
                            'usuario' => '',
                            'created_at' => ''
                        ];
                    }
                }

    
                return response()->json(array(
                    'escenarios' => $escenarios,
                    'escenario' =>  $escenario1,
                    'mesas' => $data,
                ));
            }

        }else {
            $escenarios = Escenarios::select('id', 'nombre')->get();
            $escenario1 = Escenarios::select('id', 'nombre', 'estado','created_for','updated_for')->first();

            if ($escenario1 != null) {
                $mesas = Mesas::select('id','nombre','escenarios_id','estado','relacion')->where('escenarios_id', $escenario1->id)->get();
                
                $data = [];

                foreach ($mesas as $mesa) {

                    $venta = DB::table('ventas')
                        ->join('clientes', 'ventas.clientes_id', '=', 'clientes.id')
                        ->join('users', 'ventas.user_id', '=', 'users.id')
                        ->where('ventas.mesas_id', $mesa->id)
                        ->select(
                            'ventas.fecha_pago',
                            'ventas.valor_total',
                            'ventas.created_at',
                            'clientes.razon_social as cliente',
                            'clientes.id as cliente_id',
                            'users.name as usuario'
                        )
                        ->first();

                    if ($venta != null) {
                        $data[] = [
                            'id' => $mesa->id,
                            'nombre' => $mesa->nombre,
                            'escenarios_id' => $mesa->escenarios_id,
                            'estado' => $mesa->estado,
                            'venta' => 1,
                            'fecha_pago' => $venta->fecha_pago,
                            'valor_total' => (float)$venta->valor_total,
                            'cliente' => $venta->cliente,
                            'cliente_id' => $venta->cliente_id,
                            'relacion' => $mesa->relacion,
                            'mesa_asociada' => null,
                            'usuario' => $venta->usuario,
                            'created_at' => $venta->created_at

                        ];
                    }else {
                        $data[] = [
                            'id' => $mesa->id,
                            'nombre' => $mesa->nombre,
                            'escenarios_id' => $mesa->escenarios_id,
                            'estado' => $mesa->estado,
                            'venta' => 0,
                            'fecha_pago' => '2100-05-30 12:12:00',
                            'valor_total' => '',
                            'cliente' => '',
                            'cliente_id' => '',
                            'relacion' => $mesa->relacion,
                            'mesa_asociada' => null,
                            'usuario' => '',
                            'created_at' => ''
                        ];
                    }
                }
    
                return response()->json(array(
                    'escenarios' => $escenarios,
                    'escenario' =>  $escenario1,
                    'mesas' => $data,
                ));
    
                
            }
        }
    }

    //crear escenario
    public function crearEscenario(Request $request)
    {
        $escenario = new Escenarios();
        $escenario->fill($request->all());
        $escenario->created_for = Auth::user()->id;
        $escenario->updated_for = Auth::user()->id;
        if($escenario->save()){

            $escenarios = Escenarios::select('id', 'nombre')->get();

            return response()->json(array(
                'mensaje' => 1,
                'escenarios' => $escenarios,
            ));
        }else{
            return response()->json(array(
                'mensaje' => 2
            ));
        }
    }

    //actualizar escenario
    public function updateEscenario(Request $request, $id)
    {
        $escenario = Escenarios::find($id);
        $escenario->fill($request->all());
        $escenario->updated_for = Auth::user()->id;
        if($escenario->save()){

            $escenarios = Escenarios::select('id', 'nombre')->get();

            return response()->json(array(
                'mensaje' => 1,
                'escenarios' => $escenarios,
                'escenario' => $escenario
            ));
        }else{
            return response()->json(array(
                'mensaje' => 2
            ));
        }
    }

    //cerrar mesa
    public function cerrarMesa($idMesa)
    {
        $ventas = Ventas::where('mesas_id', $idMesa)->first();
        if ($ventas != null) {
            $items = ItemsVentas::where('ventas_id', $ventas->id)->delete();
            Mesas::where('relacion',$idMesa)->update(['relacion' => NULL, 'estado' => 1]);
            if ($ventas->delete()) {
                return response()->json(array(
                    'mensaje' => 1
                ));
            }
        }else {
            return response()->json(array(
                'mensaje' => 1
            ));
        }
    }

    //obtener mesas de un escenario para unir
    public function mesasEscenario($idMesa, $idEscenario)
    {
        $mesas = Mesas::select('id','nombre','escenarios_id','estado', 'relacion')->where('escenarios_id', $idEscenario)->get();
        $data = [];
        $data2 = [];
        foreach ($mesas as $mesa) {

            $venta = DB::table('ventas')
                    ->where('ventas.mesas_id', $mesa->id)
                    ->select(
                        'id'
                    )
                    ->first();

            if ($venta == null && $mesa->id != $idMesa) {
                $data[] = [
                    'id' => $mesa->id,
                    'nombre' => $mesa->nombre,
                    'escenarios_id' => $mesa->escenarios_id,
                    'estado' => $mesa->estado,
                    'relacion' => $mesa->relacion,
                ];

                if ($mesa->relacion == $idMesa && $mesa->id != $idMesa) {
                    array_push($data2, $mesa->id);
                }
            }
        }

        return response()->json(array(
            'mesas' => $data,
            'relacionados' => $data2
        ));
    }
}
