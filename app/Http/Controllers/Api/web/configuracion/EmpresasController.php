<?php

namespace App\Http\Controllers\Api\web\configuracion;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use DB;
use Auth;
use App\Models\User;
use App\Models\Empresas;

class EmpresasController extends Controller
{
    public function show($id){
        $id = 1;
        $empresa = Empresas::find($id);
        $data = [];
        if ($empresa != null) {
          $create_for = User::find($empresa->created_for);
          $update_for = User::find($empresa->updated_for);
          $data = [
            'id' => $empresa->id,
            'ruc' => $empresa->ruc,
            'razon_social' => $empresa->razon_social,
            'nombre' => $empresa->nombre,
            'direccion' => $empresa->direccion,
            'cta_detraccion' => $empresa->cta_detraccion,
            'porcentaje_detraccion' => $empresa->porcentaje_detraccion,
            'fecha_inicio' => $empresa->fecha_inicio,
            'created_for' => $create_for->name,
            'update_for' => $update_for->name,
          ];
        }
        return response()->json($data);
    }

    public function store(Request $request){
        $empresa = Empresas::find(1);
        if ($empresa == null) {
            $empresa = new Empresas();
            $empresa->fill($request->all());
            $empresa->created_for = Auth::user()->id;
            $empresa->updated_for = Auth::user()->id;
            if($empresa->save()){
                return response()->json(array(
                    'mensaje' => 1
                ));
            }else{
                return response()->json(array(
                    'mensaje' => 3
                ));
            }
        }else{
            $empresa->fill($request->all());
            $empresa->updated_for = Auth::user()->id;
            if($empresa->save()){
                return response()->json(array(
                    'mensaje' => 2
                ));
            }else{
                return response()->json(array(
                    'mensaje' => 3
                ));
            }
        }
    }
}
