<?php

namespace App\Http\Controllers\Api\web\productos;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use DB;
use Auth;
use App\Models\User;
use App\Models\Productos;

use App\Transformers\ProductosTransformer;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use App\Models\Categorias;
use App\Models\Impuestos;
use App\Models\Unidades;

class ProductosController extends Controller
{

    private $fractal;
    private $productosTransformer;

    function __construct(Manager $fractal, ProductosTransformer $productosTransformer)
    {
        $this->fractal = $fractal;
        $this->productosTransformer = $productosTransformer;
    }

    public function index(Request $request)
    {
        //return $request->unidad_like;
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

        $codigo = '';
        if ($request->codigo_like) {
            $codigo = $request->codigo_like;
        }
        $precio_venta = '';
        if ($request->precio_venta_like) {
            $precio_venta = $request->precio_venta_like;
        }
        $stock = '';
        if ($request->stock_like) {
            $stock = $request->stock_like;
        }
        $categoria = '';
        if ($request->categoria_like) {
            $categoria = $request->categoria_like;
        }
        $impuestos_id = '';
        if ($request->impuesto_like) {
            $impuestos_id = $request->impuesto_like;
        }
        
        $unidades = '';
        if ($request->unidad_like) {
            $arrayCategoria = [];
            $dataUnidades = DB::table('unidades')->select('id')->where('nombre', 'like', '%' . $request->unidad_like . '%')->distinct('id')->get();
            foreach ($dataUnidades as $unidad) {
                array_push($arrayCategoria, $unidad->id);
            }
            $unidades = $arrayCategoria;
        }else{
            $unidades = '';
        }
        //paginacion
        $productosPaginator = Productos::
                    where('nombre', 'like', '%' . $nombre . '%')
                    ->where('categorias_id', 'like', '%' . $categoria . '%')
                    ->where('codigo', 'like', '%' . $codigo . '%')
                    ->where('impuestos_id', 'like', '%' . $impuestos_id . '%')
                    ->where('precio_venta', 'like', '%' . $precio_venta . '%')
                    ->where('stock', 'like', '%' . $stock . '%')
                    ->where(function ($query) use ($unidades) {
                        if ($unidades != '') {
                            $query->whereIn('unidades_id', $unidades);
                        }
                    })

                        ->orderby($row,$order)
                        ->paginate(10);

        $productos =  new Collection($productosPaginator->items(), $this->productosTransformer);
        $productos->setPaginator(new IlluminatePaginatorAdapter($productosPaginator));

        $productos = $this->fractal->createData($productos);
        return $productos->toArray();
    }

    public function selectCategorias(){
        $categorias = Categorias::select('id', 'nombre')->orderBy('nombre','asc')->get();
        $dataCategorias = [];
        foreach($categorias as $item){
            $dataCategorias[] = [
            'value' => $item->id,
            'title' => $item->nombre
            ];   
        }
        return $dataCategorias;
    }
    public function store(Request $request)
    {
        $producto = new Productos();
        $producto->fill($request->all());
        $producto->created_for = Auth::user()->id;
        $producto->updated_for = Auth::user()->id;
        if($producto->save()){
            return response()->json(array(
                'mensaje' => 1,
                'producto' => $producto,
            ));
        }else{
            return response()->json(array(
                'mensaje' => 2
            ));
        }
    }


    public function show($id)
    {
        $producto = Productos::find($id);
        $data = [];
        if ($producto != null) {
            $create_for = User::find($producto->created_for);
            $update_for = User::find($producto->created_for);

            $data = [
                'id' => $producto->id,
                'nombre' => $producto->nombre,
                'codigo' => $producto->codigo,
                'stock' => $producto->stock,
                'impuestos_id' => $producto->impuestos_id,
                'unidades_id' => $producto->unidades_id,
                'categorias_id' => $producto->categorias_id,
                'precio_venta' => $producto->precio_venta,
                'codigo_barra' => $producto->codigo_barra,
                'precio_mesa' => $producto->precio_mesa,
                'precio_compra' => $producto->precio_compra,
                'imagen' => $producto->imagen,
                'created_for' => $create_for->name,
                'update_for' => $update_for->name,
            ];
        }

        return response()->json($data);
    }


    public function update(Request $request, $id)
    {
        $producto = Productos::find($id);
        $producto->fill($request->all());
        $producto->updated_for = Auth::user()->id;
        if($producto->save()){
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
        $producto = Productos::find($id);
        if($producto->delete()){
            return response()->json(array(
                'mensaje' => 1
            ));
        }else{
            return response()->json(array(
                'mensaje' => 2
            ));
        }
    }

    public function datosAuxialires()
    {
        $categorias = Categorias::select('id', 'nombre')->get();
        $impuestos = Impuestos::select('id', 'nombre', 'tasa')->get();
        $unidades = Unidades::select('id', 'nombre', 'simbolo')->get();

        return response()->json(array(
            'categorias' => $categorias,
            'impuestos' => $impuestos,
            'unidades' => $unidades
        )); 
    }
}
