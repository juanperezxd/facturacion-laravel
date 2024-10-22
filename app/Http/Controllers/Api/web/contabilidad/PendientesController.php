<?php
namespace App\Http\Controllers\api\web\contabilidad;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use Auth;
use App\Models\Contratos;
use App\Models\Empresas;
//paginacion fractal
use App\Transformers\ContratosTransformer;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
class PendientesController extends Controller{
  private $fractal;
  private $contratosTransformer;
  function __construct(Manager $fractal, ContratosTransformer $contratosTransformer){
      $this->fractal = $fractal;
      $this->contratosTransformer = $contratosTransformer;
  }
  public function index(Request $request){
      //filtros
      $fecha = '';
      if ($request->fecha_like) {
          $fecha = $request->fecha_like;
      }
      $razon_social = '';
      if ($request->razon_social_like) {
          $razon_social = $request->razon_social_like;
      }
      $direccion = '';
      if ($request->direccion_like) {
          $direccion = $request->direccion_like;
      }
      $num_hoja = '';
      if ($request->num_hoja_like) {
          $num_hoja = $request->num_hoja_like;
      }
      $num_convenio = '';
      if ($request->num_convenio_like) {
          $num_convenio = $request->num_convenio_like;
      }
      $cuenta_contrato = '';
      if ($request->cuenta_contrato_like) {
          $cuenta_contrato = $request->cuenta_contrato_like;
      }
      $num_instalacion = '';
      if ($request->num_instalacion_like) {
          $num_instalacion = $request->num_instalacion_like;
      }
      $estados_id = 666;
      if ($request->estados_id_like) {
          $estados_id = $request->estados_id_like;
          if ($estados_id == 333) {
              $estados_id = 0;
          }
      }
      //paginacion
      if ($estados_id != 666) {
          $contratosPaginator = Contratos::where('fecha', 'like', '%' . $fecha . '%')
                                  ->where(DB::raw("CONCAT(`nombres`, ' ', `apellidos`, ' ', `apellido_m`)"), 'LIKE', "%".$razon_social."%")
                                  ->where(DB::raw("CONCAT_WS(`tipo_via`, ' ', `nombre_via`, ' ', `numero`, ' MZ-',`manzana`,' LT-',`lote`, ' ', `conjunto_vivienda`)"), 'LIKE', "%".$direccion."%")
                                  ->where('num_hoja', 'like', '%' . $num_hoja . '%')
                                  ->where('num_convenio', 'like', '%' . $num_convenio . '%')
                                  ->where(DB::raw('IFNULL(cuenta_contrato, 0)'), 'like', '%' . $cuenta_contrato . '%')
                                  ->where(DB::raw('IFNULL(num_instalacion, 0)'), 'like', '%' . $num_instalacion . '%')
                                  ->where('estados_id',$estados_id)
                                  ->where('boleta',NULL)
                                  ->paginate(10);
      }else {
          $contratosPaginator = Contratos::where('fecha', 'like', '%' . $fecha . '%')
                                  ->where(DB::raw("CONCAT(`nombres`, ' ', `apellidos`, ' ', `apellido_m`)"), 'LIKE', "%".$razon_social."%")
                                  ->where(DB::raw("CONCAT_WS(`tipo_via`, ' ', `nombre_via`, ' ', `numero`, ' MZ-',`manzana`,' LT-',`lote`, ' ', `conjunto_vivienda`)"), 'LIKE', "%".$direccion."%")
                                  ->where('num_hoja', 'like', '%' . $num_hoja . '%')
                                  ->where('num_convenio', 'like', '%' . $num_convenio . '%')
                                  ->where(DB::raw('IFNULL(cuenta_contrato, 0)'), 'like', '%' . $cuenta_contrato . '%')
                                  ->where(DB::raw('IFNULL(num_instalacion, 0)'), 'like', '%' . $num_instalacion . '%')
                                  ->where('estados_id','>=','11')
                                  ->where('boleta',NULL)
                                  ->paginate(10);
      }
      $contratos =  new Collection($contratosPaginator->items(), $this->contratosTransformer);
      $contratos->setPaginator(new IlluminatePaginatorAdapter($contratosPaginator));
      $contratos = $this->fractal->createData($contratos);
      return $contratos->toArray();
  }
  public function show($pendiente_id){
    $contrato=Contratos::find($pendiente_id);
    $empresa=Empresas::find(1);
    return response()->json(array(
        'contrato' => $contrato,
        'empresa' => $empresa
    ));
  }
}
