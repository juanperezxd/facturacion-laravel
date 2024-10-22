<?php

namespace App\Http\Controllers\Api\web\ventas;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use Auth;
use NumeroALetras;
use App\Models\Productos;
use App\Models\Empresas;
use App\Models\Clientes;
use App\Models\Facturas;
use App\Models\Items;
use App\Models\Series;
use Response;
use App\Models\ItemMovimientos;
use App\Models\Movimientos;
//email
use Mail;
use App\Mail\EnvioComprobante;

use DateTime;
//MODULO FACTURADOR
use App\Custom\Util;
use Greenter\Model\Client\Client;
use Greenter\Model\Company\Company;
use Greenter\Model\Company\Address;
use Greenter\Model\Sale\Invoice;
use Greenter\Model\Sale\Note;
use Greenter\Model\Sale\SaleDetail;
use Greenter\Model\Sale\Legend;
use Greenter\Ws\Services\SunatEndpoints;
use Greenter\Model\DocumentInterface;
use Greenter\Model\Response\CdrResponse;
use Greenter\Report\HtmlReport;
use Greenter\Report\PdfReport;
use Greenter\Model\Response\BillResult;
use Greenter\Model\Sale\FormaPagos\FormaPagoContado;
use Greenter\Model\Sale\FormaPagos\FormaPagoCredito;
use Greenter\Model\Sale\Cuota;
use Greenter\Model\Sale\Detraction;
use Greenter\XMLSecLibs\Certificate\X509Certificate;
use Greenter\XMLSecLibs\Certificate\X509ContentType;

//PAGINACION
use App\Transformers\VentasTransformer;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use App\Models\Ventas;
use App\Models\ItemsVentas;
use App\Models\Mesas;
//caja chica
use App\Models\Cajas;

//EVENT PUSHER
use App\Events\ActualizarCocina;

class VentasController extends Controller
{

  private $fractal;
  private $ventasTransformer;

  function __construct(Manager $fractal, VentasTransformer $ventasTransformer)
  {
      $this->fractal = $fractal;
      $this->ventasTransformer = $ventasTransformer;
  }

  public function index(Request $request){
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
      $total = '';
      if ($request->total_like) {
          $total = $request->total_like;
      }
      $fecha = '';
      if ($request->fecha_like) {
          $fecha = $request->fecha_like;
      }
      $documentos = '';
      if ($request->documentos_like) {
          $documentos = $request->documentos_like;
      }
      $estado = '';
      if ($request->estado_like) {
          $estado = $request->estado_like;
      }


      //paginacion
      $ventasPaginator = Facturas::where('cliente_setRznSocial', 'like', '%' . $razon_social . '%')
                      ->where('setMtoImpVenta', 'like', '%' . $total . '%')
                    //  ->where('setFechaEmision', 'like', '%' . $fecha . '%')
                      ->where('estadoDesc', 'like', '%' . $estado . '%')
                      ->where(DB::raw("CONCAT(`setSerie`, '-', `setCorrelativo`)"), 'LIKE', "%".$documentos."%")
                      ->where('setTipoDoc','<>','07')
                      ->orderby($row,$order)
                      ->paginate(10);

      $ventas =  new Collection($ventasPaginator->items(), $this->ventasTransformer);
      $ventas->setPaginator(new IlluminatePaginatorAdapter($ventasPaginator));

      $ventas = $this->fractal->createData($ventas);
      return $ventas->toArray();
  }


  public function store(Request $request){
    $id_producto = $request->productos[0]['id'];
    $producto_data_igv = Productos::find($id_producto);
    $tasa_igv=$producto_data_igv->impuestos->tasa;
    $carbon = new \Carbon\Carbon();
    $time = $carbon->toTimeString();
    $fecha_final = $request->fecha_pago;

    $cliente = $request->cliente;
    $productos = $request->productos;
    $fecha_pago = $fecha_final;
    $tipo_comprobante = $request->tipo_comprobante;
    $tipo_pago = $request->tipo_pago;
    $valor_total = $request->valor_total;
    $cod_referencia = $request->cod_referencia;
    $por_consumo = $request->por_consumo;
    $mesas_id = $request->mesas_id;

    if ($tipo_comprobante == 'BOLETA') {
      $serie=Series::find(1);
    }else if($tipo_comprobante == 'FACTURA'){
      $serie=Series::find(2);
    }

    $correlativo_inicial=$serie->correlativo;
    $correlativo=$this->obtenerCorrelativo($correlativo_inicial);
    $serie->correlativo=$correlativo;
    $num_cod=$serie->serie.'-'.$correlativo;
    $serie->save();
    //registramos la factura
    $factura = new Facturas();
    $factura->clientes_id = $cliente['id'];
    if ($cliente['tipo_documento'] == 'DNI') {
      $factura->cliente_setTipoDoc = 1;
    }else if($cliente['tipo_documento'] == 'RUC') {
      $factura->cliente_setTipoDoc = 6;
    }
    $factura->cliente_setNumDoc = $cliente['documento'];
    $factura->cliente_setRznSocial	 = $cliente['razon_social'];
    $factura->cliente_setDireccion = $cliente['direccion'];
    $factura->forma_pago = $request->forma_pago;
    $factura->detraccion = $request->detraccion;
    if($request->detraccion == 1){
      $empresa = Empresas::find(1);
      $factura->setTipoOperacion='1001';  
      $factura->monto_detraccion = ($valor_total * $empresa->porcentaje_detraccion) / 100;
      $factura->cod_bien_detraccion = $request->cod_bien_detraccion;
    }else{
      $factura->setTipoOperacion='0101';
    }
    
    if ($tipo_comprobante == 'BOLETA') {
        $factura->setTipoDoc = '03';
    }else if($tipo_comprobante == 'FACTURA'){
        $factura->setTipoDoc = '01';
    }
    $factura->setSerie=$serie->serie;
    $factura->setCorrelativo=$correlativo;
    $factura->setFechaEmision = $fecha_pago;
    $factura->setTipoMoneda='PEN';
    //---------------------------------------
    $letras = NumeroALetras::convertir($valor_total, 'SOLES', '');
    if($tasa_igv!=0){
      $subtotal=$valor_total/1.18;
      $preigv=$valor_total-$subtotal;
    }else{
      $subtotal=$valor_total;
      $preigv = 0;
    }
    //$valor_total= number_format($valor_total,2);

    //---------------------------------------
    if($preigv==0){
      $factura->setMtoOperGravadas=NULL;
      $factura->setMtoOperExoneradas=$subtotal;
    }else{
      $factura->setMtoOperGravadas=$subtotal;
      $factura->setMtoOperExoneradas=NULL;
    }
    //$factura->setMtoOperGravadas=$subtotal;
    $factura->setMtoIGV=$preigv;
    $factura->setTotalImpuestos=$preigv;
    $factura->setValorVenta=$subtotal;
    $factura->setMtoImpVenta=$valor_total;
    $factura->legend_setCode='1000';
    $factura->legend_setValue=$letras;
    $factura->tipo_pago = $tipo_pago;
    $factura->cod_referencia = $cod_referencia;
    $factura->por_consumo = $por_consumo;
    if($request->dias != 1){
      $factura->dias = $request->dias;
    }
    
    if ($factura->save()) {
      foreach ($productos as $producto) {
        $items = new Items();
        $items->facturas_id = $factura->id;
        $items->productos_id = $producto['id'];
        $items->setCodProducto = $producto['codigo'];
        $items->setUnidad='NIU';
        $items->setCantidad = $producto['cantidad'];
        $items->setDescripcion = $producto['nombre'];
        //---------------------------------------
        $cantidad=$producto['cantidad'];
        //$precio=$producto['precio'];
        $precio=$producto['total']/$producto['cantidad'];
        
        $producto_id = $producto['id'];
        $producto_data = Productos::find($producto_id);
        $tasa=$producto_data->impuestos->tasa;
        //$tasa=$producto->productos->impuestos->tasa;
        if($tasa!=0){
            $items->setTipAfeIgv='10';
            $subtotalItem=$precio/1.18;
            $igvItem=$precio-$subtotalItem;
        }else{
            $items->setTipAfeIgv='20';
            $subtotalItem=$precio;
            $igvItem=$precio-$subtotalItem;
        }
        
        
        // $subtotalItem=number_format($precio/1.18,2);
        // $igvItem=number_format($precio-$subtotalItem,2);
        //---------------------------------------
        $items->setMtoBaseIgv=$subtotalItem*$cantidad;
        $items->setPorcentajeIgv='18';
        $items->setIgv=$igvItem*$cantidad;
        //$items->setTipAfeIgv='10';
        $items->setTotalImpuestos=$igvItem*$cantidad;
        $items->setMtoValorVenta=$subtotalItem*$cantidad;
        $items->setMtoValorUnitario=$subtotalItem;
        $items->setMtoPrecioUnitario=$precio;
        $items->setTotal = $precio;
        //$operacion = floatval($producto['cantidad']) * floatval($producto['precio']);
        //$items->setMtoValorVenta = $operacion;
        $items->save();
      }
      if ($factura->setTipoDoc == '03') {
        $response=$this->generar_boleta($factura->id);
      }else if($factura->setTipoDoc == '01'){
        if($factura->detraccion == 1){
          $response=$this->generar_factura_detraccion($factura->id);  
        }else if($factura->forma_pago == 'CREDITO' && $factura->detraccion == '0'){
          $response=$this->generar_factura_credito($factura->id);
        }else if($factura->forma_pago == 'CREDITO' && $factura->detraccion == '1'){
          $response=$this->generar_factura_credito_detraccion($factura->id);
        }else{
          $response=$this->generar_factura($factura->id);
        }
        
      }

      //salida movimiento
      $movimiento = new Movimientos();
      $movimiento->tipo='RE';
      $movimiento->tipo_doc = $tipo_comprobante;
      $movimiento->tipo_movimiento = 'VENTA';
      $movimiento->facturas_id = $factura->id;
      $movimiento->clientes_id = $factura->clientes_id;
      $movimiento->num_doc = $factura->cliente_setNumDoc;
      $movimiento->fecha = $request->fecha_pago;
      if($movimiento->save()){
        $movimiento_id=$movimiento->id;
        foreach ($productos as $producto) {
          $producto_id=$producto['id'];  //<---
          $cantidad=$producto['cantidad'];//<---
          //OBTENER SALDO
          $saldo=0; //<---
          $precio=0;
          $tipo='RE'; //<--
          $items=ItemMovimientos::PorProducto($producto_id)->get();
          $cant=count($items);
          foreach($items as $item){
            $precio+=$item->precio;
            if($item->tipo=='IN'){
              $saldo+=$item->cantidad;
            }elseif($item->tipo=='DE'){
              $saldo+=$item->cantidad;
            }else{
              $saldo-=$item->cantidad;
            }
          }
          $saldo=$saldo-$cantidad;
          //CREACION DE ITEMS
          $itenMovimiento=new ItemMovimientos();
          $itenMovimiento->movimientos_id=$movimiento_id;
          $itenMovimiento->productos_id=$producto_id;
          $itenMovimiento->cantidad=$cantidad;
          $itenMovimiento->saldo=$saldo;
          $itenMovimiento->precio=$precio/($cant+1);
          $itenMovimiento->tipo=$tipo;
          if($itenMovimiento->save()){
            $producto=Productos::find($producto_id);
            $producto->stock=$saldo;
            $producto->save();
          }
        }
      }

      //generar ingreso en caja chica
      $caja = new Cajas();
      $caja->tipomovimiento = 'INGRESO';
      $caja->nombres = $cliente['razon_social'];
      $caja->descripcion = 'POR CONSUMO';
      $caja->fecha = $request->fecha_pago;
      $caja->hora = $time;
      $caja->tipo_pago = $tipo_pago;
      $caja->monto = $valor_total;
      $caja->cierre = 'NO';
      $caja->user_id = Auth::user()->id;
      $caja->facturas_id = $factura->id;
      $caja->save();

      //cerrar mesas
      //$this->cerrarMesa($mesas_id);
      return response()->json(array(
          'envio' => $request->all(),
          'mensaje' => 1,
          'factura' => $factura,
          'responseSunat' =>$response
      ));
    }
  }


  //datos productos
  public function datosProductosVenta()
  {
      $productos = Productos::select('id', 'nombre', 'codigo', 'precio_venta', 'imagen', 'codigo_barra', 'precio_mesa')->get();

      return response()->json(array(
          'productos' => $productos,
      ));
  }
  //datos clientes
  public function datosClientesVenta()
  {
      $clientes = Clientes::all();

      return response()->json(array(
          'clientes' => $clientes
      ));
  }

  public function show($id)
  {
      $this->generar_pdf($id);
      $factura = Facturas::find($id);
      
      $items = Items::where('facturas_id', $id)->get();

      return response()->json(array(
        'factura' => $factura,
        'items' => $items
      ));
  }



//FACTURACION ELECTRONICA
  function generar_boleta($factura_id){
      //OBTENCION DE DATOS.
      $factura=Facturas::find($factura_id);
      $items=$factura->items;
      //GENERACION DE BOLETA
      $util = Util::getInstance();
      $see = $util->conseguirDatos();
      // Cliente
      $client = new Client();
      $client->setTipoDoc($factura->cliente_setTipoDoc)
      ->setNumDoc($factura->cliente_setNumDoc)
      ->setAddress((new Address())
              ->setDireccion($factura->cliente_setDireccion))
      ->setRznSocial($factura->cliente_setRznSocial);
      // Venta
      $invoice = new Invoice();

      $invoice
      ->setUblVersion('2.1')
      ->setTipoOperacion($factura->setTipoOperacion)
      ->setTipoDoc($factura->setTipoDoc)
      ->setSerie($factura->setSerie)
      ->setCorrelativo(intval($factura->setCorrelativo))
      ->setFechaEmision(new \DateTime($factura->setFechaEmision))
      ->setTipoMoneda($factura->setTipoMoneda)
      ->setClient($client)
      ->setMtoOperGravadas($factura->setMtoOperGravadas)
      ->setMtoOperExoneradas($factura->setMtoOperExoneradas)
      ->setMtoIGV($factura->setMtoIGV)
      ->setTotalImpuestos($factura->setTotalImpuestos)
      ->setValorVenta($factura->setValorVenta)
      ->setMtoImpVenta($factura->setMtoImpVenta)
      ->setCompany($util->getCompany());
      //Items de la venta
      $datas= array();
      foreach ($items as $item) {
        $item1 = new SaleDetail();
        $item1->setCodProducto($item->setCodProducto)
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
      //Leyenda
      $legend = new Legend();
      $legend->setCode($factura->legend_setCode)
      ->setValue($factura->legend_setValue);
      //agregado de items y leyendas al documento.
      $invoice->setDetails($datas)
      ->setLegends([$legend]);
      $xml=$see->getXmlSigned($invoice);
      $response=$util->writeXml($invoice, $xml);
      $editFactura=Facturas::find($factura_id);
      $editFactura->estado=1;
      $editFactura->estadoDesc='PROCESADA';
      $editFactura->document_name=$response;
      $editFactura->save();
      $pdf = $util->getPdf($invoice, "CONTADO", 0, "", "", 0);

      /*
      // Envio a SUNAT.
      //$see = $util->getSee(SunatEndpoints::FE_BETA);
      ////$see = $util->getSee(SunatEndpoints::FE_PRODUCCION);
      $res = $see->send($invoice);
      $util->writeXml($invoice, $see->getFactory()->getLastXml());
      $cdr = $res->getCdrResponse();
      if ($res->isSuccess()) {
        @var $res \Greenter\Model\Response\BillResult
        $cdr = $res->getCdrResponse();
        $document_name=$util->writeCdr($invoice, $res->getCdrZip());
        $code=$cdr->getCode();
        $descripcion=$cdr->getDescription();
        $editFactura=Facturas::find($factura_id);
        $editFactura->estado=$code;
        $editFactura->estadoDesc=$descripcion;
        $editFactura->document_name=$document_name;
        $editFactura->save();
        $pdf = $util->getPdf($invoice);
        //$util->showPdf($pdf, $invoice->getName().'.pdf');
        //echo $util->getResponseFromCdr($cdr);
        //echo $code;
      } else {
        $errRes=$res->getError();
        $code=$errRes->getCode();
        $descripcion=$errRes->getMessage();
        $editFactura=Facturas::find($factura_id);
        $editFactura->estado=$code;
        $editFactura->estadoDesc=$descripcion;
        $editFactura->save();
        var_dump($res->getError());
      }*/
  }
  function reenviar_factura($factura_id){
    $factura = Facturas::find($factura_id);
    if($factura->detraccion == 1 && $factura->forma_pago == 'CONTADO'){
      $response=$this->generar_factura_detraccion($factura_id);
    }else if($factura->forma_pago == 'CREDITO' && $factura->detraccion == 0){
      $response=$this->generar_factura_credito($factura_id);
    }else if($factura->forma_pago == 'CREDITO' && $factura->detraccion == 1){
      $response=$this->generar_factura_credito_detraccion($factura_id);
    }else{
      $response=$this->generar_factura($factura_id);
    }
    
  }
  function generar_factura($factura_id){
    //OBTENCION DE DATOS.
    $factura=Facturas::find($factura_id);
    $factura_id=$factura->id;
    $items=$factura->items;
    //GENERACION DE FACTURA
    $util = Util::getInstance();
    $see = $util->conseguirDatos();
    // Cliente
    $client = new Client();
    $client->setTipoDoc($factura->cliente_setTipoDoc)
    ->setNumDoc($factura->cliente_setNumDoc)
    ->setAddress((new Address())
            ->setDireccion($factura->cliente_setDireccion))
    ->setRznSocial($factura->cliente_setRznSocial);
    // Venta
    $util = Util::getInstance();
    $invoice = (new Invoice())
    ->setUblVersion('2.1')
    ->setTipoOperacion($factura->setTipoOperacion) // Catalog. 51
    ->setTipoDoc($factura->setTipoDoc)
    ->setSerie($factura->setSerie)
    ->setCorrelativo($factura->setCorrelativo)
    ->setFormaPago(new FormaPagoContado())
    ->setFechaEmision(new \DateTime($factura->setFechaEmision))
    ->setTipoMoneda($factura->setTipoMoneda)
    ->setClient($client)
    ->setMtoOperGravadas($factura->setMtoOperGravadas)
    ->setMtoOperExoneradas($factura->setMtoOperExoneradas)
    ->setMtoIGV($factura->setMtoIGV)
    ->setTotalImpuestos($factura->setTotalImpuestos)
    ->setValorVenta($factura->setValorVenta)
    ->setSubTotal($factura->setMtoImpVenta)
    ->setMtoImpVenta($factura->setMtoImpVenta)
    ->setCompany($util->getCompany());
    //ITEM
    $datas= array();
    foreach ($items as $item) {
      $item1 = new SaleDetail();
      $item1->setCodProducto($item->setCodProducto)
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
    //LEYENDA
    $legend = new Legend();
    $legend->setCode($factura->legend_setCode)
    ->setValue($factura->legend_setValue);
    //agregado de items y leyendas al documento.
    $invoice->setDetails($datas)
    ->setLegends([$legend]);
    // Envio a SUNAT.
    $see = $util->getSee(SunatEndpoints::FE_BETA);
    //$see = $util->getSee(SunatEndpoints::FE_PRODUCCION);
    $res = $see->send($invoice);
    $util->writeXml($invoice, $see->getFactory()->getLastXml());
    if ($res->isSuccess()) {
      $cdr = $res->getCdrResponse();
      $document_name=$util->writeCdr($invoice, $res->getCdrZip());
      $code=$cdr->getCode();
      $descripcion=$cdr->getDescription();
      $editFactura=Facturas::find($factura_id);
      $editFactura->estado=$code;
      $editFactura->estadoDesc=$descripcion;
      $editFactura->document_name=$document_name;
      $editFactura->save();

      $movimientos = Movimientos::where('facturas_id', $factura_id)->where('tipo_doc', 'GUIA DE REMISION')->first();
      if($movimientos != null){
        $guia = $movimientos->num_doc;
      }else{
        $guia = '';
      }
      $pdf = $util->getPdf($invoice, $factura->forma_pago, $factura->detraccion, '' , $guia, '');
      //$util->showPdf($pdf, $invoice->getName().'.pdf');
      //echo $util->getResponseFromCdr($cdr);
    } else {
      $errRes=$res->getError();
      $code=$errRes->getCode();
      $descripcion=$errRes->getMessage();

      $editFactura=Facturas::find($factura_id);
      $editFactura->estado=$code;
      $editFactura->estadoDesc=$descripcion;
      $editFactura->save();
      var_dump($res->getError());
    }
  }

  function generar_factura_credito($factura_id){
    //OBTENCION DE DATOS.
    $factura=Facturas::find($factura_id);
    $factura_id=$factura->id;
    $items=$factura->items;
    //GENERACION DE FACTURA
    $util = Util::getInstance();
    $see = $util->conseguirDatos();
    // Cliente
    $client = new Client();
    $client->setTipoDoc($factura->cliente_setTipoDoc)
    ->setNumDoc($factura->cliente_setNumDoc)
    ->setAddress((new Address())
            ->setDireccion($factura->cliente_setDireccion))
    ->setRznSocial($factura->cliente_setRznSocial);
    // Venta
    $util = Util::getInstance();
    $invoice = (new Invoice())
    ->setUblVersion('2.1')
    ->setTipoOperacion($factura->setTipoOperacion) // Catalog. 51
    ->setTipoDoc($factura->setTipoDoc)
    ->setSerie($factura->setSerie)
    ->setCorrelativo($factura->setCorrelativo)
    ->setFormaPago(new FormaPagoCredito($factura->setMtoImpVenta))
      ->setCuotas([
        (new Cuota())
          ->setMonto($factura->setMtoImpVenta)
          ->setFechaPago(new DateTime('+' . $factura->dias . 'days'))
    ])
    ->setFechaEmision(new DateTime($factura->setFechaEmision))
    ->setTipoMoneda($factura->setTipoMoneda)
    ->setClient($client)
    ->setMtoOperGravadas($factura->setMtoOperGravadas)
    ->setMtoOperExoneradas($factura->setMtoOperExoneradas)
    ->setMtoIGV($factura->setMtoIGV)
    ->setTotalImpuestos($factura->setTotalImpuestos)
    ->setValorVenta($factura->setValorVenta)
    ->setSubTotal($factura->setMtoImpVenta)
    ->setMtoImpVenta($factura->setMtoImpVenta)
    ->setCompany($util->getCompany());
    //ITEM
    $datas= array();  
    foreach ($items as $item) {
      $item1 = new SaleDetail();
      $item1->setCodProducto($item->setCodProducto)
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
    //LEYENDA
    $legend = new Legend();
    $legend->setCode($factura->legend_setCode)
    ->setValue($factura->legend_setValue);
    //agregado de items y leyendas al documento.
    $invoice->setDetails($datas)
    ->setLegends([$legend]);
    // Envio a SUNAT.
    $see = $util->getSee(SunatEndpoints::FE_BETA);
    //$see = $util->getSee(SunatEndpoints::FE_PRODUCCION);
    $res = $see->send($invoice);
    $util->writeXml($invoice, $see->getFactory()->getLastXml());
    if ($res->isSuccess()) {
      $cdr = $res->getCdrResponse();
      $document_name=$util->writeCdr($invoice, $res->getCdrZip());
      $code=$cdr->getCode();
      $descripcion=$cdr->getDescription();
      $editFactura=Facturas::find($factura_id);
      $editFactura->estado=$code;
      $editFactura->estadoDesc=$descripcion;
      $editFactura->document_name=$document_name;
      $editFactura->dias_credito = new DateTime('+' . $factura->dias . 'days');
      $editFactura->save();

      $movimientos = Movimientos::where('facturas_id', $factura_id)->where('tipo_doc', 'GUIA DE REMISION')->first();
      if($movimientos != null){
        $guia = $movimientos->num_doc;
      }else{
        $guia = '';
      }
      $dias_factura = Facturas::find($factura_id);

      $pdf = $util->getPdf($invoice, $factura->forma_pago, $factura, $dias_factura->dias_credito, $guia, '');
      //$util->showPdf($pdf, $invoice->getName().'.pdf');
      //echo $util->getResponseFromCdr($cdr);
    } else {
      $errRes=$res->getError();
      $code=$errRes->getCode();
      $descripcion=$errRes->getMessage();

      $editFactura=Facturas::find($factura_id);
      $editFactura->estado=$code;
      $editFactura->estadoDesc=$descripcion;
      $editFactura->save();
      var_dump($res->getError());
    }
  }
  function generar_factura_credito_detraccion($factura_id){
    //OBTENCION DE DATOS.
    $empresa =  Empresas::find(1);
    $factura=Facturas::find($factura_id);
    $factura_id=$factura->id;
    $items=$factura->items;
    //GENERACION DE FACTURA
    $util = Util::getInstance();
    $see = $util->conseguirDatos();
    // Cliente
    $client = new Client();
    $client->setTipoDoc($factura->cliente_setTipoDoc)
    ->setNumDoc($factura->cliente_setNumDoc)
    ->setAddress((new Address())
            ->setDireccion($factura->cliente_setDireccion))
    ->setRznSocial($factura->cliente_setRznSocial);
    // Venta
    $util = Util::getInstance();
    $invoice = (new Invoice())
    ->setUblVersion('2.1')
    ->setTipoOperacion($factura->setTipoOperacion) // Catalog. 51
    ->setTipoDoc($factura->setTipoDoc)
    ->setSerie($factura->setSerie)
    ->setCorrelativo($factura->setCorrelativo)
    ->setFormaPago(new FormaPagoCredito($factura->setMtoImpVenta))
      ->setCuotas([
        (new Cuota())
          ->setMonto($factura->setMtoImpVenta)
          ->setFechaPago(new DateTime('+' . $factura->dias . 'days'))
    ])
    ->setDetraccion(
      // MONEDA SIEMPRE EN SOLES
          (new Detraction())
              // Carnes y despojos comestibles
              ->setCodBienDetraccion($factura->cod_bien_detraccion) // catalog. 54
              // Deposito en cuenta
              ->setCodMedioPago('001') // catalog. 59
              ->setCtaBanco($empresa->cta_detraccion)
              ->setPercent($empresa->porcentaje_detraccion)
              ->setMount(($factura->setMtoImpVenta * $empresa->porcentaje_detraccion) / 100)
    )
    ->setFechaEmision(new DateTime($factura->setFechaEmision))
    ->setTipoMoneda($factura->setTipoMoneda)
    ->setClient($client)
    ->setMtoOperGravadas($factura->setMtoOperGravadas)
    ->setMtoOperExoneradas($factura->setMtoOperExoneradas)
    ->setMtoIGV($factura->setMtoIGV)
    ->setTotalImpuestos($factura->setTotalImpuestos)
    ->setValorVenta($factura->setValorVenta)
    ->setSubTotal($factura->setMtoImpVenta)
    ->setMtoImpVenta($factura->setMtoImpVenta)
    ->setCompany($util->getCompany());
    //ITEM
    $datas= array();  
    foreach ($items as $item) {
      $item1 = new SaleDetail();
      $item1->setCodProducto($item->setCodProducto)
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
    //LEYENDA
    $legend = new Legend();
    $legend->setCode($factura->legend_setCode)
    ->setValue($factura->legend_setValue);
    $legend2 = new Legend();
    $legend2->setCode('2006')
    ->setValue('Operación sujeta a detraccion');
    //agregado de items y leyendas al documento.
    $invoice->setDetails($datas)
    ->setLegends([$legend, $legend2]);
    // Envio a SUNAT.
    $see = $util->getSee(SunatEndpoints::FE_BETA);
    //$see = $util->getSee(SunatEndpoints::FE_PRODUCCION);
    $res = $see->send($invoice);
    $util->writeXml($invoice, $see->getFactory()->getLastXml());
    if ($res->isSuccess()) {
      $cdr = $res->getCdrResponse();
      $document_name=$util->writeCdr($invoice, $res->getCdrZip());
      $code=$cdr->getCode();
      $descripcion=$cdr->getDescription();
      $editFactura=Facturas::find($factura_id);
      $editFactura->estado=$code;
      $editFactura->estadoDesc=$descripcion;
      $editFactura->document_name=$document_name;
      $editFactura->dias_credito = new DateTime('+' . $factura->dias . 'days');
      $editFactura->save();

      $movimientos = Movimientos::where('facturas_id', $factura_id)->where('tipo_doc', 'GUIA DE REMISION')->first();
      if($movimientos != null){
        $guia = $movimientos->num_doc;
      }else{
        $guia = '';
      }
      $dias_factura = Facturas::find($factura_id);

      $pdf = $util->getPdf($invoice, $factura->forma_pago, $factura, $dias_factura->dias_credito, $guia, $dias_factura->detraccion);
      //$util->showPdf($pdf, $invoice->getName().'.pdf');
      //echo $util->getResponseFromCdr($cdr);
    } else {
      $errRes=$res->getError();
      $code=$errRes->getCode();
      $descripcion=$errRes->getMessage();

      $editFactura=Facturas::find($factura_id);
      $editFactura->estado=$code;
      $editFactura->estadoDesc=$descripcion;
      $editFactura->save();
      var_dump($res->getError());
    }
  }
  function generar_factura_detraccion($factura_id){
    //OBTENCION DE DATOS.
    $empresa =  Empresas::find(1);
    $factura=Facturas::find($factura_id);
    $factura_id=$factura->id;
    $items=$factura->items;
    //GENERACION DE FACTURA
    $util = Util::getInstance();
    $see = $util->conseguirDatos();
    // Cliente
    $client = new Client();
    $client->setTipoDoc($factura->cliente_setTipoDoc)
    ->setNumDoc($factura->cliente_setNumDoc)
    ->setAddress((new Address())
            ->setDireccion($factura->cliente_setDireccion))
    ->setRznSocial($factura->cliente_setRznSocial);
    // Venta
    $util = Util::getInstance();
    $invoice = (new Invoice())
    ->setUblVersion('2.1')
    ->setTipoOperacion($factura->setTipoOperacion) // Catalog. 51
    ->setTipoDoc($factura->setTipoDoc)
    ->setSerie($factura->setSerie)
    ->setCorrelativo($factura->setCorrelativo)
    ->setFormaPago(new FormaPagoContado())
    ->setFechaEmision(new \DateTime($factura->setFechaEmision))
    ->setTipoMoneda($factura->setTipoMoneda)
    ->setClient($client)
    ->setMtoOperGravadas($factura->setMtoOperGravadas)
    ->setMtoOperExoneradas($factura->setMtoOperExoneradas)
    ->setMtoIGV($factura->setMtoIGV)
    ->setTotalImpuestos($factura->setTotalImpuestos)
    ->setValorVenta($factura->setValorVenta)
    ->setSubTotal($factura->setMtoImpVenta)
    ->setMtoImpVenta($factura->setMtoImpVenta)
    ->setDetraccion(
      // MONEDA SIEMPRE EN SOLES
          (new Detraction())
              // Carnes y despojos comestibles
              ->setCodBienDetraccion($factura->cod_bien_detraccion) // catalog. 54
              // Deposito en cuenta
              ->setCodMedioPago('001') // catalog. 59
              ->setCtaBanco($empresa->cta_detraccion)
              ->setPercent($empresa->porcentaje_detraccion)
              ->setMount(($factura->setMtoImpVenta * $empresa->porcentaje_detraccion) / 100)
    )
    ->setCompany($util->getCompany());
    //ITEM
    $datas= array();
    foreach ($items as $item) {
      $item1 = new SaleDetail();
      $item1->setCodProducto($item->setCodProducto)
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
    //LEYENDA
    $legend = new Legend();
    $legend->setCode($factura->legend_setCode)
    ->setValue($factura->legend_setValue);
    $legend2 = new Legend();
    $legend2->setCode('2006')
    ->setValue('Operacion sujeta a detraccion');
    //agregado de items y leyendas al documento.
    $invoice->setDetails($datas)
    ->setLegends([$legend, $legend2]);
    // Envio a SUNAT.
    $see = $util->getSee(SunatEndpoints::FE_BETA);
    //$see = $util->getSee(SunatEndpoints::FE_PRODUCCION);
    $res = $see->send($invoice);
    $util->writeXml($invoice, $see->getFactory()->getLastXml());
    if ($res->isSuccess()) {
      $cdr = $res->getCdrResponse();
      $document_name=$util->writeCdr($invoice, $res->getCdrZip());
      $code=$cdr->getCode();
      $descripcion=$cdr->getDescription();
      $editFactura=Facturas::find($factura_id);
      $editFactura->estado=$code;
      $editFactura->estadoDesc=$descripcion;
      $editFactura->document_name=$document_name;
      $editFactura->save();
      $movimientos = Movimientos::where('facturas_id', $factura_id)->where('tipo_doc', 'GUIA DE REMISION')->first();
      if($movimientos != null){
        $guia = $movimientos->num_doc;
      }else{
        $guia = '';
      }
      $pdf = $util->getPdf($invoice, $factura->forma_pago, $factura->detraccion, '' ,$guia, '');
      //$util->showPdf($pdf, $invoice->getName().'.pdf');
      //echo $util->getResponseFromCdr($cdr);
    } else {
      $errRes=$res->getError();
      $code=$errRes->getCode();
      $descripcion=$errRes->getMessage();

      $editFactura=Facturas::find($factura_id);
      $editFactura->estado=$code;
      $editFactura->estadoDesc=$descripcion;
      $editFactura->save();
      var_dump($res->getError());
    }
  }
  function reenviar_nota($factura_id){
    $this->generar_nota($factura_id);
  }
  function generar_nota($factura_id){
    $factura=Facturas::find($factura_id);
    $util = Util::getInstance();
    $see = $util->conseguirDatos();
    $items=$factura->items;
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
      $util = Util::getInstance();
      $note = new Note();
      $note
      ->setUblVersion('2.1')
      ->setTipDocAfectado($factura->setTipDocAfectado)//01 FACTURA || 03 BOLETA DE VENTA || 07 NOTA DE CREDITO
      ->setNumDocfectado($factura->setNumDocfectado)
      ->setCodMotivo($factura->setCodMotivo)  //01 Anulación de la operación   02 Anulación por error en el RUC   03 Corrección por error en la descripción  04 Descuento global  05 Descuento por ítem  06 Devolución total  07 Devolución por ítem  08 Bonificación  09 Disminución en el valor  10 Otros Conceptos
      ->setDesMotivo($factura->setDesMotivo)
      ->setTipoDoc($factura->setTipoDoc)
      ->setSerie($factura->setSerie)
      ->setFechaEmision(new \DateTime($factura->setFechaEmision))
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
        $item1 = new SaleDetail();
        $item1->setCodProducto($item->setCodProducto)
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
      if($factura->setTipDocAfectado=='03'){
        $xml=$see->getXmlSigned($note);
        $response=$util->writeXml($note, $xml);
        $editFactura=Facturas::find($factura_id);
        $editFactura->estado='1';
        $editFactura->estadoDesc='PROCESADA';
        $editFactura->document_name=$response;
        $editFactura->save();
      }else if($factura->setTipDocAfectado=='01'){
        // Envio a SUNAT.
        $see = $util->getSee(SunatEndpoints::FE_BETA);
        //$see = $util->getSee(SunatEndpoints::FE_PRODUCCION);
        $res = $see->send($note);
        $util->writeXml($note, $see->getFactory()->getLastXml());

        if ($res->isSuccess()){
          $cdr = $res->getCdrResponse();
          $document_name=$util->writeCdr($note, $res->getCdrZip());
          $code=$cdr->getCode();
          $descripcion=$cdr->getDescription();
          $editFactura=Facturas::find($factura_id);
          $editFactura->estado=$code;
          $editFactura->estadoDesc=$descripcion;
          $editFactura->document_name=$document_name;
          $editFactura->save();
        } else {
          $errRes=$res->getError();
          $code=$errRes->getCode();
          $descripcion=$errRes->getMessage();
          $editFactura=Facturas::find($factura_id);
          $editFactura->estado=$code;
          $editFactura->estadoDesc=$descripcion;
          $editFactura->save();
          //var_dump($res->getError());
        }
      }
    }
  }
  function obtenerCorrelativo($correlativo_inicial){
    $largo=strlen($correlativo_inicial);
    $numero=intval($correlativo_inicial)+1;
    $num_largo=strlen($numero);
    $ceros=$largo-$num_largo;
    $numero=(string) $numero;
    for ($i=1; $i <= $ceros; $i++){
      $numero='0'.$numero;
    }
    return $numero;
  }
  //DESCARGAS
  public function downloadPDF($pdf){
    //$file= public_path(). "/greenter/files/".$pdf.".pdf";
    $file=  "/home/softgasa/public_html/apimultiservicio/greenter/files/".$pdf.".html";
    $headers = array(
      'Content-Type: application/html',
    );
    return Response::download($file, $pdf.'.html', $headers);
  }
  public function downloadXML($xml){
    //$file= public_path(). "/greenter/files/".$xml.".xml";
    $file=  "/home/softgasa/public_html/apimultiservicio/greenter/files/".$xml.".xml";
    //$file= url('/'). "/greenter/files/".$xml.".xml";

    return Response::download($file, $xml.'.xml');
  }
  public function downloadCRD($crd){
    $cdr='R-'.$crd;
    //$file= public_path(). "/greenter/files/".$cdr.".zip";
    $file=  "/home/softgasa/public_html/apimultiservicio/greenter/files/".$cdr.".zip";
    $headers = array(
      'Content-Type: application/zip',
    );
    return Response::download($file, $cdr.'.zip', $headers);
  }

  //ANULACION COMPROBANTE
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
  	//$contrato_id=$factura_anular->contratos_id;
  	$num_documento=$factura_anular->setSerie.'-'.$factura_anular->setCorrelativo;
  	$serie=Series::find(3);
  	$correlativo_inicial=$serie->correlativo;
  	$correlativo=$this->obtenerCorrelativo($correlativo_inicial);
  	$serie->correlativo=$correlativo;
  	$num_cod=$serie->serie.'-'.$correlativo;
  	if($serie->save()){
  		$factura = new Facturas();
  		//$factura->contratos_id=$contrato_id;
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
  		$factura->setMtoOperExoneradas=$factura_anular->setMtoOperExoneradas;
  		$factura->setMtoIGV=$factura_anular->setMtoIGV;
  		$factura->setTotalImpuestos=$factura_anular->setTotalImpuestos;
  		$factura->setValorVenta=$factura_anular->setValorVenta;
  		$factura->setMtoImpVenta=$factura_anular->setMtoImpVenta;
  		$factura->legend_setCode=1000;
  		$factura->legend_setValue=$factura_anular->legend_setValue;
  		$factura->estado='1';
  		$factura->estadoDesc='PROCESADO';
  		if($factura->save()){
            foreach ($factura_anular->items as $item){
              $itemN=new Items();
              $itemN->facturas_id=$factura->id;
              $itemN->setCodProducto=$item->setCodProducto;
              $itemN->setUnidad=$item->setUnidad;
              $itemN->setCantidad=$item->setCantidad;
              $itemN->setDescripcion=$item->setDescripcion;
              $itemN->setMtoBaseIgv=$item->setMtoBaseIgv;
              $itemN->setPorcentajeIgv=$item->setPorcentajeIgv;
              $itemN->setIgv=$item->setIgv;
              $itemN->setTipAfeIgv=$item->setTipAfeIgv;
              $itemN->setTotalImpuestos=$item->setTotalImpuestos;
			  $itemN->setMtoValorVenta=$item->setMtoValorVenta;
			  $itemN->setMtoValorUnitario=$item->setMtoValorUnitario;
			  $itemN->setMtoPrecioUnitario=$item->setMtoPrecioUnitario;
              $itemN->save();
            }
  			$factura_anular->estado='3';
  			$factura_anular->estadoDesc='ANULADA';
  			if($factura_anular->save()){
                $this->generar_nota($factura->id);
                return response()->json(array(
                  'mensaje' => 1,
                  'factura_estado' => $factura_anular->estadoDesc,
                  'estado' => $factura_anular->estado
                ));
  			}
  		}
    }
  }

  public function anularFactura($factura_id){
    $carbon = new \Carbon\Carbon();
  	$date = $carbon->now();
  	$hoy=$date->format('Y-m-d');
  	$factura_anular=Facturas::find($factura_id);
    $factura_anular=Facturas::find($factura_id);
    $cliente_setTipoDoc=$factura_anular->cliente_setTipoDoc;
    $cliente_setNumDoc=$factura_anular->cliente_setNumDoc;
    $cliente_setRznSocial=$factura_anular->cliente_setRznSocial;
    $cliente_setDireccion=$factura_anular->cliente_setDireccion;
    $setCodProducto=$factura_anular->items[0]->setCodProducto;
    $setDescripcion=$factura_anular->items[0]->setDescripcion;
    //$contrato_id=$factura_anular->contratos_id;
    $num_documento=$factura_anular->setSerie.'-'.$factura_anular->setCorrelativo;
    $serie=Series::find(4);
    $correlativo_inicial=$serie->correlativo;
    $correlativo=$this->obtenerCorrelativo($correlativo_inicial);
    $serie->correlativo=$correlativo;
    $num_cod=$serie->serie.'-'.$correlativo;
    if($serie->save()){
      $factura = new Facturas();
      //$factura->contratos_id=$contrato_id;
      $factura->setTipDocAfectado='01';
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
      $factura->setMtoOperExoneradas=$factura_anular->setMtoOperExoneradas;
      $factura->setMtoIGV=$factura_anular->setMtoIGV;
      $factura->setTotalImpuestos=$factura_anular->setTotalImpuestos;
      $factura->setValorVenta=$factura_anular->setValorVenta;
      $factura->setMtoImpVenta=$factura_anular->setMtoImpVenta;
      $factura->legend_setCode=1000;
      $factura->legend_setValue=$factura_anular->legend_setValue;
      $factura->estado='1';
      $factura->estadoDesc='PROCESADO';
      if($factura->save()){
        foreach ($factura_anular->items as $item){
          $itemN=new Items();
          $itemN->facturas_id=$factura->id;
          $itemN->setCodProducto=$item->setCodProducto;
          $itemN->setUnidad=$item->setUnidad;
          $itemN->setCantidad=$item->setCantidad;
          $itemN->setDescripcion=$item->setDescripcion;
          $itemN->setMtoBaseIgv=$item->setMtoBaseIgv;
          $itemN->setPorcentajeIgv=$item->setPorcentajeIgv;
          $itemN->setIgv=$item->setIgv;
          $itemN->setTipAfeIgv=$item->setTipAfeIgv;
          $itemN->setTotalImpuestos=$item->setTotalImpuestos;
          $itemN->setMtoValorVenta=$item->setMtoValorVenta;
          $itemN->setMtoValorUnitario=$item->setMtoValorUnitario;
          $itemN->setMtoPrecioUnitario=$item->setMtoPrecioUnitario;
          $itemN->save();
        }
        $factura_anular->estado='3';
        $factura_anular->estadoDesc='ANULADA';
        if($factura_anular->save()){
         $this->generar_nota($factura->id);
         return response()->json(array(
           'mensaje' => 1,
              'factura_estado' => $factura_anular->estadoDesc,
              'estado' => $factura_anular->estado
         ));
        }
        
      }
    }
  }


  //FUNCIONES DE VENTAS
  public function guardarVenta(Request $request)
  {

    $carbon = new \Carbon\Carbon();
    $time = $carbon->toTimeString();
    $fecha_final = $request->fecha_pago;

    $cliente = $request->cliente;
    $productos = $request->productos;
    $fecha_pago = $fecha_final;
    $tipo_comprobante = $request->tipo_comprobante;
    $tipo_pago = $request->tipo_pago;
    $valor_total = $request->valor_total;
    $cod_referencia = $request->cod_referencia;
    $por_consumo = $request->por_consumo;
    $mesas_id = $request->mesas_id;
    $mesas = $request->mesas;

    $venta = Ventas::where('mesas_id', $mesas_id)->first();

    if ($venta != null) {
      //elimianar items_ventas
      $itemsVentas = ItemsVentas::where('ventas_id', $venta->id)->delete();
      //actualar la venta
      if ($cliente != '' && $cliente != null) {
        $venta->clientes_id = $cliente['id'];
      }
      $venta->mesas_id = $mesas_id;
      $venta->fecha_pago = $fecha_pago;
      $venta->tipo_comprobante = $tipo_comprobante;
      $venta->tipo_pago = $tipo_pago;
      $venta->valor_total = $valor_total;
      $venta->cod_referencia = $cod_referencia;
      $venta->por_consumo = $por_consumo;
      $venta->user_id = Auth::user()->id;

      //guardamos los nuevos productos de la venta
      if ($venta->save()) {
        foreach ($productos as $producto) {
          $item = new ItemsVentas();
          $item->ventas_id = $venta->id;
          $item->productos_id = $producto['id'];
          $item->codigo_producto = $producto['codigo'];
          $item->cantidad = $producto['cantidad'];
          $item->descripcion = $producto['nombre'];
          $item->precio_unitario = $producto['precio'];
          $item->precio_mesa = $producto['precio_modificado'];
          $item->total = $producto['total'];
          $item->save();
        }

        if (count($mesas) > 0) {
          foreach ($mesas as $mesa) {
            Mesas::where('id',$mesa['id'])->update(['relacion' => $mesas_id, 'estado' => 0]);
          }
        }else {
          Mesas::where('relacion',$mesas_id)->update(['relacion' => NULL, 'estado' => 1]);
        }
      }

      //pusher
      $confirmar = 1;
      $mesaPush = $venta->mesas->nombre;

      //test event
      event(new ActualizarCocina($confirmar, $mesaPush));

      return response()->json(array(
        'envio' => $request->all(),
        'mensaje' => 1
      ));

    }else {
      $venta = new Ventas();
      if ($cliente != '' && $cliente != null) {
        $venta->clientes_id = $cliente['id'];
      }
      $venta->mesas_id = $mesas_id;
      $venta->fecha_pago = $fecha_pago;
      $venta->tipo_comprobante = $tipo_comprobante;
      $venta->tipo_pago = $tipo_pago;
      $venta->cod_referencia = $cod_referencia;
      $venta->valor_total = $valor_total;
      $venta->por_consumo = $por_consumo;
      $venta->user_id = Auth::user()->id;
      if ($venta->save()) {
        foreach ($productos as $producto) {
          $item = new ItemsVentas();
          $item->ventas_id = $venta->id;
          $item->productos_id = $producto['id'];
          $item->codigo_producto = $producto['codigo'];
          $item->cantidad = $producto['cantidad'];
          $item->descripcion = $producto['nombre'];
          $item->precio_unitario = $producto['precio'];
          $item->precio_mesa = $producto['precio_modificado'];
          $item->total = $producto['total'];
          $item->save();
        }

        //guardar mesas asociadas
        if (count($mesas) > 0) {
          foreach ($mesas as $mesa) {
            Mesas::where('id',$mesa['id'])->update(['relacion' => $mesas_id, 'estado' => 0]);
          }
        }

      }

      //pusher
      $confirmar = 1;
      $mesaPush = $venta->mesas->nombre;
      //test event
      event(new ActualizarCocina($confirmar, $mesaPush));

      return response()->json(array(
        'envio' => $request->all(),
        'mensaje' => 1
      ));
    }



  }
  public function getVenta($idMesa)
  {
    $venta = Ventas::where('mesas_id', $idMesa)->first();

    if ($venta != null) {

      $items = ItemsVentas::where('ventas_id', $venta->id)->get();

      return response()->json(array(
        'mensaje' => 1,
        'venta' => $venta,
        'items' => $items
      ));

    }else {
      return response()->json(array(
        'mensaje' => 2
      ));
    }
  }
  //eliminar venta y sus items
  public function cerrarMesa($idMesa)
  {
    if ($idMesa != 0) {
      $ventas = Ventas::where('mesas_id', $idMesa)->first();
      if ($ventas != null) {
        $items = ItemsVentas::where('ventas_id', $ventas->id)->delete();
      }
      $ventas->delete();
    }

  }

  //enviar comprobante
  public function enviarComprobante(Request $request){
    if ($request->has('cliente') && $request->cliente != '') {
      $facturas = Facturas::find($request->factura);
      if ($facturas != null) {
        Mail::to($request->cliente)->send(new EnvioComprobante($facturas));
        return response()->json(array(
          'mensaje' => 1
        ));
      }
    }
  }
  public function enviarComprobanteTest($cliente,$factura){
      $facturas = Facturas::find($factura);
      if ($facturas != null) {
        Mail::to($cliente)->send(new EnvioComprobante($facturas));
        return response()->json(array(
          'mensaje' => 1
        ));
      }
  }

//FUNCIONES DE VENTAS CON GUIAS
  public function buscarGuias($cliente_id){
    $data = [];
    $movimientos = Movimientos::with('itemMovimientos')->where('tipo_doc','GUIA DE REMISION')->where('clientes_id',$cliente_id)->where('facturado','NO')->where('tipo','RE')->get();
    foreach($movimientos as $movimiento){
      $subtotal=0;
      $igv=0;
      $total=0;
      foreach ($movimiento->itemMovimientos as $item){
        $precio=$item->cantidad*$item->precio;
        $tasa=$item->productos->impuestos->tasa;
        if($tasa!=0){
          $subIgv=$precio-($precio/(1.18));
        }else{
          $subIgv=0;
        }

        $sub=$precio-$subIgv;
        $subtotal+=$sub;
        $igv+=$subIgv;
        $total+=$item->cantidad*$item->precio;
      }
      $data [] = [
        'id'=>$movimiento->id,
        'tipo_doc'=>$movimiento->tipo_doc,
        'num_doc'=>$movimiento->num_doc,
        'fecha'=>$movimiento->fecha,
        'facturado'=>$movimiento->facturado,
        'subtotal' => $subtotal,
        'igv' => $igv,
        'total' => $total,
      ];
    }
    return response()->json(array(
      'movimientos' => $data,
    ));
  }
  public function datosGuia($movimiento_id){
    $movimiento=Movimientos::find($movimiento_id);
    $items=ItemMovimientos::where('movimientos_id',$movimiento_id)->get();
    $proveedor=$movimiento->clientes->razon_social;
    $rucProv=$movimiento->clientes->documento;
    return response()->json(array(
      'movimiento' => $movimiento,
      'items' => $items,
      'proveedor' => $proveedor,
      'rucProv' => $rucProv

    ));
  }
  public function emitirFacturaDeGuias($codigos,$valor_total,$igv,$subtotal){


    $myString = substr($codigos, 0, -1);
    $sections = explode(',', $myString);
    $movimento=Movimientos::find($sections[0]);
    $productos = DB::table('item_movimientos')
           ->select(DB::raw('productos_id,sum(cantidad) as cantidad,precio'))
           ->whereIn('movimientos_id', $sections)
           ->groupBy('productos_id')
           ->groupBy('precio')
           ->get();



   $cliente=$movimento->clientes;

   $carbon = new \Carbon\Carbon();
   $time = $carbon->toTimeString();
   $date = $carbon->now();
   $fecha_final=$date->format('Y-m-d');
   $fecha_pago = $fecha_final;
   $por_consumo = 0;

   $serie=Series::find(2);


   $correlativo_inicial=$serie->correlativo;
   $correlativo=$this->obtenerCorrelativo($correlativo_inicial);
   $serie->correlativo=$correlativo;
   $num_cod=$serie->serie.'-'.$correlativo;
   $serie->save();

   //registramos la factura
   $factura = new Facturas();
   $factura->clientes_id = $cliente['id'];
   if ($cliente['tipo_documento'] == 'DNI') {
     $factura->cliente_setTipoDoc = 1;
   }else if($cliente['tipo_documento'] == 'RUC') {
     $factura->cliente_setTipoDoc = 6;
   }
   $factura->cliente_setNumDoc = $cliente['documento'];
   $factura->cliente_setRznSocial	 = $cliente['razon_social'];
   $factura->cliente_setDireccion = $cliente['direccion'];
   $factura->setTipoOperacion='0101';
   $factura->setTipoDoc = '01';
   $factura->setSerie=$serie->serie;
   $factura->setCorrelativo=$correlativo;
   $factura->setFechaEmision = $fecha_pago;
   $factura->setTipoMoneda='PEN';
   //---------------------------------------
   $letras  = NumeroALetras::convertir(number_format($valor_total,2), 'SOLES', '');

   //---------------------------------------
   $factura->setMtoOperGravadas=$subtotal;
   $factura->setMtoIGV=$igv;
   $factura->setTotalImpuestos=$igv;
   $factura->setValorVenta=$subtotal;
   $factura->setMtoImpVenta=$valor_total;
   $factura->legend_setCode='1000';
   $factura->legend_setValue=$letras;
   $factura->tipo_pago = 'EFECTIVO';
   $factura->cod_referencia = NULL;
   $factura->por_consumo = $por_consumo;
   if ($factura->save()) {
     foreach ($productos as $product) {
       $prod_id=$product->productos_id;
       $cant=$product->cantidad;

       $producto=Productos::find($prod_id);



       $items = new Items();
       $items->facturas_id = $factura->id;
       $items->productos_id = $producto['id'];
       $items->setCodProducto = $producto['codigo'];
       $items->setUnidad='NIU';
       $items->setCantidad = $cant;
       $items->setDescripcion = $producto['nombre'];
       //---------------------------------------
       $cantidad=$cant;
       $precio=$product->precio;
       $tasa=$producto->impuestos->tasa;
       if($tasa!=0){
         $items->setTipAfeIgv='10';
         $subtotal=$precio/1.18;
         $igv=$precio-$subtotal;
       }else{
         $items->setTipAfeIgv='20';
         $subtotal=$precio;
         $igv=$precio-$subtotal;
       }
       //---------------------------------------
       $items->setMtoBaseIgv=$subtotal*$cantidad;
       $items->setPorcentajeIgv=$tasa;
       $items->setIgv=$igv*$cantidad;
       $items->setTotalImpuestos=$igv*$cantidad;
       $items->setMtoValorVenta=$subtotal*$cantidad;
       $items->setMtoValorUnitario=$subtotal;
       $items->setMtoPrecioUnitario=$precio;
       $items->setTotal = $precio*$cantidad;
       $items->save();
     }
     $response=$this->generar_factura($factura->id);

     DB::table('movimientos')->whereIn('id', $sections)->update(array('facturado' => 'SI'));

     //generar ingreso en caja chica
     $caja = new Cajas();
     $caja->tipomovimiento = 'INGRESO';
     $caja->nombres = $cliente['razon_social'];
     $caja->descripcion = 'POR CONSUMO';
     $caja->fecha = $fecha_pago;
     $caja->hora = $time;
     $caja->tipo_pago = 'EFECTIVO';
     $caja->monto = $valor_total;
     $caja->cierre = 'NO';
     $caja->user_id = Auth::user()->id;
     $caja->facturas_id = $factura->id;
     $caja->save();

     return response()->json(array(
         'mensaje' => 1,
         'factura' => $factura,
         'responseSunat' =>$response
     ));

   }
  }
  
  public function generar_pdf($factura_id){
    $factura=Facturas::find($factura_id);
    $items=$factura->items;
    $util = Util::getInstance();

  /*  if($factura->estado!='0'){
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
      ->setFechaEmision(new \DateTime($factura->setFechaEmision))
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
      //ITEM
      $datas= array();
      foreach($items as $item){
        $item1 = new SaleDetail();
        $item1->setCodProducto($item->setCodProducto)
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

      if($factura->detraccion == 1){
          $legend = new Legend();
          $legend->setCode($factura->legend_setCode)
          ->setValue($factura->legend_setValue);
          $legend2 = new Legend();
          $legend2->setCode('2006')
          ->setValue('Operacion sujeta a detraccion');
          //agregado de items y leyendas al documento.
          $note->setDetails($datas)
          ->setLegends([$legend, $legend2]);    
      }else{
          $legend = new Legend();
          $legend->setCode($factura->legend_setCode)
          ->setValue($factura->legend_setValue);
          $note->setDetails($datas)
          ->setLegends([$legend]);    
      }
      

      
      
      try {
          $movimientos = Movimientos::where('facturas_id', $factura_id)->where('tipo_doc', 'GUIA DE REMISION')->first();
          if($movimientos != null){
              $guia = $movimientos->num_doc;
          }else{
              $guia = '';
          }
        if($factura->forma_pago == 'CONTADO'){
          $pdf = $util->getPdf($note, $factura->forma_pago, $factura->detraccion, '', $guia, '');
        }else{
          $dias_factura = Facturas::find($factura_id);
          $pdf = $util->getPdf($note, $factura->forma_pago, $factura, $dias_factura->dias_credito, $guia, $dias_factura->detraccion);
        }
        return response()->json(array(
            'nombrePDF' => $pdf
        ));
      } catch (Exception $e) {
          var_dump($e);
      }
    //}*/
  }


}
