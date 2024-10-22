<?php

namespace App\Http\Controllers\Api\web\ventas;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\Facturas;

//PAGINACION
use App\Transformers\VentasTransformer;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;

class FacturacionController extends Controller
{
    private $fractal;
  private $ventasTransformer;

  function __construct(Manager $fractal, VentasTransformer $ventasTransformer)
  {
      $this->fractal = $fractal;
      $this->ventasTransformer = $ventasTransformer;
  }

  public function boletas(Request $request)
  {
      $order='DESC';
      if ($request->_order) {
          $order = $request->_order;
      }
      $row='id';
      if ($request->_sort) {
          $row = $request->_sort;
      }
      //filtros
      $razon_social = '';
      if ($request->razon_social_like) {
          $razon_social = $request->razon_social_like;
      }

      //paginacion
      $ventasPaginator = Facturas::where('cliente_setRznSocial', 'like', '%' . $razon_social . '%')
                      ->orderby($row,$order)
                      ->where('setTipoDoc', '03')
                      ->paginate(10);

      $ventas =  new Collection($ventasPaginator->items(), $this->ventasTransformer);
      $ventas->setPaginator(new IlluminatePaginatorAdapter($ventasPaginator));

      $ventas = $this->fractal->createData($ventas);
      return $ventas->toArray();
  }

  public function facturas(Request $request)
  {
      $order='DESC';
      if ($request->_order) {
          $order = $request->_order;
      }
      $row='id';
      if ($request->_sort) {
          $row = $request->_sort;
      }
      //filtros
      $razon_social = '';
      if ($request->razon_social_like) {
          $razon_social = $request->razon_social_like;
      }

      //paginacion
      $ventasPaginator = Facturas::where('cliente_setRznSocial', 'like', '%' . $razon_social . '%')
                      ->where('setTipoDoc', '01')
                      ->orderby($row,$order)
                      ->paginate(10);

      $ventas =  new Collection($ventasPaginator->items(), $this->ventasTransformer);
      $ventas->setPaginator(new IlluminatePaginatorAdapter($ventasPaginator));

      $ventas = $this->fractal->createData($ventas);
      return $ventas->toArray();
  }
}
