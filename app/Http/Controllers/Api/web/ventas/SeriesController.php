<?php

namespace App\Http\Controllers\Api\web\ventas;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\Series;
//PAGINACION
use App\Transformers\SeriesTransformer;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;

class SeriesController extends Controller
{
    private $fractal;
    private $seriesTransformer;

    function __construct(Manager $fractal, SeriesTransformer $seriesTransformer)
    {
        $this->fractal = $fractal;
        $this->seriesTransformer = $seriesTransformer;
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
        $seriesPaginator = Series::where('descripcion', 'like', '%' . $descripcion . '%')
                        ->orderby($row,$order)
                        ->paginate(10);

        $series =  new Collection($seriesPaginator->items(), $this->seriesTransformer);
        $series->setPaginator(new IlluminatePaginatorAdapter($seriesPaginator));

        $series = $this->fractal->createData($series);
        return $series->toArray();
    }

    public function store(Request $request)
    {
        //
    }

    public function show($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        //
    }

    public function destroy($id)
    {
        //
    }
}
