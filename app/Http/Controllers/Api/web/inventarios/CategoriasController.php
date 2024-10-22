<?php

namespace App\Http\Controllers\Api\web\inventarios;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use DB;
use Auth;
use App\Models\User;

use App\Transformers\CategoriasTransformer;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use App\Models\Categorias;

class CategoriasController extends Controller
{
    private $fractal;
    private $categoriasTransformer;

    function __construct(Manager $fractal, CategoriasTransformer $categoriasTransformer)
    {
        $this->fractal = $fractal;
        $this->categoriasTransformer = $categoriasTransformer;
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
        $categoriasPaginator = Categorias::where('nombre', 'like', '%' . $nombre . '%')
                        ->orderby($row,$order)
                        ->paginate(10);

        $categorias =  new Collection($categoriasPaginator->items(), $this->categoriasTransformer);
        $categorias->setPaginator(new IlluminatePaginatorAdapter($categoriasPaginator));

        $categorias = $this->fractal->createData($categorias);
        return $categorias->toArray();
    }

    public function store(Request $request)
    {
        $categoria = new Categorias();
        $categoria->fill($request->all());
        $categoria->estado = 1;
        $categoria->created_for = Auth::user()->id;
        $categoria->updated_for = Auth::user()->id;
        if($categoria->save()){
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
        $categoria = Categorias::find($id);
        $data = [];
        if ($categoria != null) {
            $create_for = User::find($categoria->created_for);
            $update_for = User::find($categoria->created_for);

            $data = [
                'id' => $categoria->id,
                'nombre' => $categoria->nombre,
                'descripcion' => $categoria->descripcion,
                'estado' => $categoria->estado,
                'created_for' => $create_for->name,
                'update_for' => $update_for->name,
            ];
        }

        return response()->json($data);
    }


    public function update(Request $request, $id)
    {
        $categoria = Categorias::find($id);
        $categoria->fill($request->all());
        $categoria->estado = 1;
        $categoria->updated_for = Auth::user()->id;
        if($categoria->save()){
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
        $categoria = Categorias::find($id);
        if($categoria->delete()){
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
