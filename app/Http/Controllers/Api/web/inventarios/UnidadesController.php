<?php

namespace App\Http\Controllers\Api\web\inventarios;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use DB;
use Auth;
use App\Models\User;

use App\Transformers\UnidadesTransformer;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use App\Models\Unidades;

class UnidadesController extends Controller
{
    private $fractal;
    private $unidadesTransformer;

    function __construct(Manager $fractal, UnidadesTransformer $unidadesTransformer)
    {
        $this->fractal = $fractal;
        $this->unidadesTransformer = $unidadesTransformer;
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
        $nombre = '';
        if ($request->nombre_like) {
            $nombre = $request->nombre_like;
        }

        //paginacion
        $unidadesPaginator = Unidades::where('nombre', 'like', '%' . $nombre . '%')
                        ->orderby($row,$order)
                        ->paginate(10);

        $unidades =  new Collection($unidadesPaginator->items(), $this->unidadesTransformer);
        $unidades->setPaginator(new IlluminatePaginatorAdapter($unidadesPaginator));

        $unidades = $this->fractal->createData($unidades);
        return $unidades->toArray();
    }

    public function store(Request $request)
    {
        $unidades = new Unidades();
        $unidades->fill($request->all());
        $unidades->created_for = Auth::user()->id;
        $unidades->updated_for = Auth::user()->id;
        if($unidades->save()){
            return response()->json(array(
                'mensaje' => 1,
            ));
        }else{
            return response()->json(array(
                'mensaje' => 2
            ));
        }
    }


    public function show($id)
    {
        $unidades = Unidades::find($id);
        $data = [];
        if ($unidades != null) {
            $create_for = User::find($unidades->created_for);
            $update_for = User::find($unidades->created_for);

            $data = [
                'id' => $unidades->id,
                'nombre' => $unidades->nombre,
                'simbolo' => $unidades->simbolo,
                'estado' => $unidades->estado,
                'created_for' => $create_for->name,
                'update_for' => $update_for->name,
            ];
        }

        return response()->json($data);
    }


    public function update(Request $request, $id)
    {
        $unidad = Unidades::find($id);
        $unidad->fill($request->all());
        $unidad->updated_for = Auth::user()->id;
        if($unidad->save()){
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
        $unidad = Unidades::find($id);
        if($unidad->delete()){
            return response()->json(array(
                'mensaje' => 1
            ));
        }else{
            return response()->json(array(
                'mensaje' => 2
            ));
        }
    }
}
