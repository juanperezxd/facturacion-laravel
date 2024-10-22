<?php

namespace App\Http\Controllers\api\web\contabilidad;

use DB;
use Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Series;

//paginacion fractal
use App\Transformers\SeriesTransformer;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;

class SeriesController extends Controller{
  private $fractal;
  private $seriesTransformer;

  function __construct(Manager $fractal, SeriesTransformer $seriesTransformer){
      $this->fractal = $fractal;
      $this->seriesTransformer = $seriesTransformer;
  }
  public function index(Request $request){
    //filtros
    //filtros
    $order='ASC';
    if ($request->_order){$order = $request->_order;}
    $row='id';
    if ($request->_sort){$row = $request->_sort;}



    //paginacion
    $seriesPaginator = Series::orderby($row,$order)
                            ->paginate(10);

    $series =  new Collection($seriesPaginator->items(), $this->seriesTransformer);
    $series->setPaginator(new IlluminatePaginatorAdapter($seriesPaginator));

    $series = $this->fractal->createData($series);
    return $series->toArray();
  }
  public function store(Request $request)
  {
      $serie = new Series();
      $serie->fill($request->all());

      if($serie->save()){
          return response()->json(array(
              'mensaje' => 1,
              'malla' => $serie->id
          ));
      }else{
          return response()->json(array(
              'mensaje' => 2
          ));
      }
  }
  public function show($id){
    $serie = Series::find($id);
    $data = [];
    if ($serie != null) {
        $data = [
            'id' => $serie->id,
            'descripcion' => $serie->descripcion,
            'tipo_documento' => $serie->tipo_documento,
            'serie' => $serie->serie,
            'correlativo' => $serie->correlativo,
            'estado' => $serie->estado,
        ];
    }
    return response()->json($data);
  }
  public function update(Request $request, $id){
    $serie = Series::find($id);
    $serie->fill($request->all());
    if($serie->save()){
      return response()->json(array(
        'mensaje' => 1
      ));
    }else{
      return response()->json(array(
        'mensaje' => 2
      ));
    }
  }

  public function destroy($id){
    $serie = Series::find($id);
    if($serie->delete()){
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
