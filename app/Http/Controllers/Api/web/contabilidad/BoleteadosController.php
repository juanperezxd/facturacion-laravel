<?php

namespace App\Http\Controllers\api\web\contabilidad;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Greenter\Model\Client\Client;
use Greenter\Model\Company\Company;
use Greenter\Model\Company\Address;
use Greenter\Model\Sale\Invoice;
use Greenter\Model\Sale\Note;
use Greenter\Model\Sale\SaleDetail;
use Greenter\Model\Sale\Legend;
use Greenter\Ws\Services\SunatEndpoints;
use Greenter\Data\StoreTrait;
use Greenter\Model\DocumentInterface;
use Greenter\Model\Response\CdrResponse;
use Greenter\Report\HtmlReport;
use Greenter\Report\PdfReport;
use Response;
use App\Custom\Util;

use DB;
use Auth;
use App\Models\Facturas;
use App\Models\Empresas;
use App\Models\Items;
use App\Models\Contratos;
use App\Models\Series;


//paginacion fractal
use App\Transformers\FacturasTransformer;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;

class BoleteadosController extends Controller{
  private $fractal;
  private $facturasTransformer;

  function __construct(Manager $fractal, FacturasTransformer $facturasTransformer){
      $this->fractal = $fractal;
      $this->facturasTransformer = $facturasTransformer;
  }

  public function index(Request $request){
    //filtros
    $order='DESC';
    if ($request->_order) { $order = $request->_order; }
    $row='id';
    if ($request->_sort) { $row = $request->_sort; }
    $id = '';
    if($request->id_like){ $id = $request->id_like; }
    $razon_social = '';
    if($request->razon_social_like){ $razon_social = $request->razon_social_like; }
    $precio = '';
    if($request->precio_like){ $precio = $request->precio_like; }
    $boleta = '';
    if($request->boleta_like){ $boleta = $request->boleta_like; }
    $fecha = '';
    if($request->fecha_like){ $fecha = $request->fecha_like; }
    $estado = '';
    if($request->estado_like){ $estado = $request->estado_like; }


    //paginacion
    $facturasPaginator = Facturas::where('id', 'like', '%' . $id . '%')
                                 ->where('cliente_setRznSocial', 'like', '%' . $razon_social . '%')
                                 ->where('setMtoImpVenta', 'like', '%' . $precio . '%')
                                 ->where(DB::raw("CONCAT(`setSerie`, '-', `setCorrelativo`)"), 'LIKE', "%".$boleta."%")
                                 ->where('setFechaEmision', 'like', '%' . $fecha . '%')
                                 ->where('setTipoDoc','03')
                                 ->where('estadoDesc', 'like', '%' . $estado . '%')
                                 ->orderby($row,$order)
                                 ->paginate(10);


    $facturas =  new Collection($facturasPaginator->items(), $this->facturasTransformer);
    $facturas->setPaginator(new IlluminatePaginatorAdapter($facturasPaginator));

    $facturas = $this->fractal->createData($facturas);
    return $facturas->toArray();
  }
  public function show($factura_id){
    $factura=Facturas::find($factura_id);
    $empresa=Empresas::find(1);
    return response()->json(array(
        'factura' => $factura,
        'empresa' => $empresa
    ));
  }
  //FUNCIONES PARA AULAR BOLETAS
  public function anularBoleta($factura_id){
  	$carbon = new \Carbon\Carbon();
  	$date = $carbon->now();
  	$hoy=$date->format('Y-m-d');
  	$factura_anular=Facturas::find($factura_id);
  	$cliente_setTipoDoc=$factura_anular->cliente_setTipoDoc;
  	$cliente_setNumDoc=$factura_anular->cliente_setNumDoc;
  	$cliente_setRznSocial=$factura_anular->cliente_setRznSocial;
  	$cliente_setDireccion=$factura_anular->cliente_setDireccion;
  	$setCodProducto=$factura_anular->items[0]->setCodProducto;
  	$setDescripcion=$factura_anular->items[0]->setDescripcion;
  	$contrato_id=$factura_anular->contratos_id;
  	$num_documento=$factura_anular->setSerie.'-'.$factura_anular->setCorrelativo;
  	$serie=Series::find(2);
  	$correlativo_inicial=$serie->correlativo;
  	$correlativo=$this->obtenerCorrelativo($correlativo_inicial);
  	$serie->correlativo=$correlativo;
  	$num_cod=$serie->serie.'-'.$correlativo;
  	if($serie->save()){
  		$factura = new Facturas();
  		$factura->contratos_id=$contrato_id;
  		$factura->setTipDocAfectado='03';
      $factura->setNumDocfectado=$num_documento;
  		$factura->setCodMotivo='07';
  		$factura->setDesMotivo='ANULACIÓN DE LA OPERACIÓN';
  		$factura->setTipoDoc='07';
  		$factura->setSerie=$serie->serie;
  		$factura->setFechaEmision=$hoy;
  		$factura->setCorrelativo=$correlativo;
  		$factura->setTipoMoneda='PEN';
  		$factura->cliente_setTipoDoc=$cliente_setTipoDoc;
  		$factura->cliente_setNumDoc=$cliente_setNumDoc;
  		$factura->cliente_setRznSocial=$cliente_setRznSocial;
  		$factura->cliente_setDireccion=$cliente_setDireccion;
  		$factura->setMtoOperGravadas=$factura_anular->setMtoOperGravadas;
  		$factura->setMtoIGV=$factura_anular->setMtoIGV;
  		$factura->setTotalImpuestos=$factura_anular->setTotalImpuestos;
  		$factura->setValorVenta=$factura_anular->setValorVenta;
  		$factura->setMtoImpVenta=$factura_anular->setMtoImpVenta;
  		$factura->legend_setCode=1000;
  		$factura->legend_setValue=$factura_anular->legend_setValue;
  		$factura->estado='1';
  		$factura->estadoDesc='PROCESADO';
  		if($factura->save()){
  			$item=new Items();
  			$item->facturas_id=$factura->id;
  			$item->setCodProducto=$setCodProducto;
  			$item->setUnidad=$factura_anular->items[0]->setUnidad;
  			$item->setCantidad=1;
  			$item->setDescripcion=$factura_anular->items[0]->setDescripcion;
  			$item->setMtoBaseIgv=$factura_anular->items[0]->setMtoBaseIgv;
  			$item->setPorcentajeIgv=$factura_anular->items[0]->setPorcentajeIgv;
  			$item->setIgv=$factura_anular->items[0]->setIgv;
  			$item->setTipAfeIgv=$factura_anular->items[0]->setTipAfeIgv;
  			$item->setTotalImpuestos=$factura_anular->items[0]->setTotalImpuestos;
  			$item->setMtoValorVenta=$factura_anular->items[0]->setMtoValorVenta;
  			$item->setMtoValorUnitario=$factura_anular->items[0]->setMtoValorUnitario;
  			$item->setMtoPrecioUnitario=$factura_anular->items[0]->setMtoPrecioUnitario;
  			if($item->save()){
  				$factura_anular->estado='3';
  				$factura_anular->estadoDesc='ANULADA';
  				if($factura_anular->save()){
  					$contrato=Contratos::find($contrato_id);
  					$contrato->boleta=NULL;
  					if($contrato->save()){
  						if($this->generar_nota($factura->id)==1){
  							return response()->json([
  								"mensaje" => $factura->id
  							]);
  						}
  					}
  				}
  			}
  		}
  	}
  }
  function generar_nota($factura_id){
  	$factura=Facturas::find($factura_id);
  	$items=$factura->items;
  	$util = Util::getInstance();
  	$see = $util->conseguirDatos();
  	if($factura->estado!='0'){
  		/*->setGuias([ (new Document()) ->setTipoDoc('09')  ->setNroDoc('001-213') ])*/
  		// Cliente----------------------------------------
  		$client = new Client();
  		$client->setTipoDoc($factura->cliente_setTipoDoc)
  		->setNumDoc($factura->cliente_setNumDoc)
      ->setAddress((new Address())
              ->setDireccion($factura->cliente_setDireccion))  
  		->setRznSocial($factura->cliente_setRznSocial);
  		//------------------------------------------------
  		$note = new Note();
  		$note
  		->setUblVersion('2.1')
  		->setTipDocAfectado($factura->setTipDocAfectado)//01 FACTURA || 03 BOLETA DE VENTA || 07 NOTA DE CREDITO
  		->setNumDocfectado($factura->setNumDocfectado)
  		->setCodMotivo($factura->setCodMotivo)  //01 Anulación de la operación   02 Anulación por error en el RUC   03 Corrección por error en la descripción  04 Descuento global  05 Descuento por ítem  06 Devolución total  07 Devolución por ítem  08 Bonificación  09 Disminución en el valor  10 Otros Conceptos
  		->setDesMotivo($factura->setDesMotivo)
  		->setTipoDoc($factura->setTipoDoc)
  		->setSerie($factura->setSerie)
  		->setFechaEmision(new \DateTime())
  		->setCorrelativo($factura->setCorrelativo)
  		->setTipoMoneda($factura->setTipoMoneda)

  		->setClient($client)
  		->setMtoOperGravadas($factura->setMtoOperGravadas)
  		->setMtoOperExoneradas($factura->setMtoOperExoneradas)
  		->setMtoOperInafectas($factura->setMtoOperInafectas)
  		->setMtoIGV($factura->setMtoIGV)
  		->setTotalImpuestos($factura->setTotalImpuestos)
  		->setMtoImpVenta($factura->setMtoImpVenta)
  		->setCompany($util->getCompany());
  	    $datas= array();
        foreach ($items as $item) {
            $item = new SaleDetail();
            $item->setCodProducto($item->setCodProducto)
            ->setUnidad($item->setUnidad)
            ->setCantidad($item->setCantidad)
            ->setDescripcion($item->setDescripcion)
            ->setMtoBaseIgv($item->setMtoBaseIgv)
            ->setPorcentajeIgv($item->setPorcentajeIgv)
            ->setIgv($item->setIgv)
            ->setTipAfeIgv($item->setTipAfeIgv)
            ->setTotalImpuestos($item->setTotalImpuestos)
            ->setMtoValorVenta($item->setMtoValorVenta)
            ->setMtoValorUnitario($item->setMtoValorUnitario)
            ->setMtoPrecioUnitario($item->setMtoPrecioUnitario);  
            $datas[]=$item1;
        }

  		
  		$legend = new Legend();
  		$legend->setCode($factura->legend_setCode)
  		->setValue($factura->legend_setValue);
  		$note->setDetails($datas)
  		->setLegends([$legend]);
  		$xml=$see->getXmlSigned($note);
  		$document_name=$util->writeXml($note, $xml);
  		$editFactura=Facturas::find($factura_id);
  		$editFactura->document_name=$document_name;
  		if($editFactura->save()){
  			return 1;
  		}
  	}
  }
  function obtenerCorrelativo($correlativo_inicial){
  	$largo=strlen($correlativo_inicial);
  	$numero=intval($correlativo_inicial)+1;
  	$num_largo=strlen($numero);
  	$ceros=$largo-$num_largo;
  	$numero=(string) $numero;
  	return $numero;
  }
}
