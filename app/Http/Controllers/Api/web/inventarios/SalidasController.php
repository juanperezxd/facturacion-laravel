<?php

namespace App\Http\Controllers\Api\web\inventarios;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


use DateTime;
use App\Models\Series;
use App\Models\Facturas;
use App\Models\Items;
use App\Models\Clientes;
use App\Models\Productos;
use App\Models\Movimientos;
use App\Models\ItemMovimientos;
use App\Models\Empresas;
use NumeroALetras;
use App\Transformers\SalidasTransformer;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;

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
use Greenter\Data\StoreTrait;
use Greenter\Model\DocumentInterface;
use Greenter\Model\Response\CdrResponse;
use Greenter\Report\HtmlReport;
use Greenter\Report\PdfReport;
use Greenter\Model\Sale\FormaPagos\FormaPagoContado;
use Greenter\Model\Sale\FormaPagos\FormaPagoCredito;
use Greenter\Model\Sale\Cuota;
use Greenter\Model\Sale\Detraction;

//GUIA DE REMISION
use Greenter\Model\Despatch\AdditionalDoc;
use Greenter\Model\Despatch\Despatch;
use Greenter\Model\Despatch\DespatchDetail;
use Greenter\Model\Despatch\Direction;
use Greenter\Model\Despatch\Shipment;
use Greenter\Model\Despatch\Transportist;



use Greenter\XMLSecLibs\Certificate\X509Certificate;
use Greenter\XMLSecLibs\Certificate\X509ContentType;
use Illuminate\Support\Facades\DB;

class SalidasController extends Controller
{
    private $fractal;
    private $salidasTransformer;

    function __construct(Manager $fractal, SalidasTransformer $salidasTransformer){
        $this->fractal = $fractal;
        $this->salidasTransformer = $salidasTransformer;
    }

    public function index(Request $request){
        //filtros
        $order='DESC'; if ($request->_order) { $order = $request->_order;  }
        $row='id'; if ($request->_sort) { $row = $request->_sort; }
        $id= '';          if($request->id_like){ $id=$request->id_like;  }
        $tipo_doc= '';    if($request->tipo_doc_like){ $tipo_doc=$request->tipo_doc_like;  }
        $num_doc= '';     if($request->num_doc_like){ $num_doc=$request->num_doc_like;  }
        $proveedor= '';   if($request->proveedor_like){ $proveedor=$request->proveedor_like;  }
        $fecha= '';       if($request->fecha_like){ $fecha=$request->fecha_like;  }

        $salidasPaginator = Movimientos::where('id','like', '%' . $id . '%')
                                      ->where('tipo_doc','like', '%' . $tipo_doc. '%')
                                      ->where('num_doc','like', '%' .$num_doc . '%')
                                      //->where('fecha','like', '%' . $fecha . '%')
                                      ->where('tipo','RE')
                                      ->whereHas('clientes',function($q)use($proveedor){
                                        $q->where('razon_social','like', '%' .$proveedor. '%');
                                        })
                                      ->orderby($row,$order)
                                     ->paginate(10);

            $movimientos =  new Collection($salidasPaginator->items(), $this->salidasTransformer);
            $movimientos->setPaginator(new IlluminatePaginatorAdapter($salidasPaginator));

            $movimientos = $this->fractal->createData($movimientos);
            return $movimientos->toArray();

    }

    public function store(Request $request){
        //$colaborador_id=$request->colaboradores_id;
        $movimiento = new Movimientos();
        $movimiento->fill($request->all());
        $movimiento->tipo_movimiento = 'SALIDA';
        $movimiento->tipo='RE';
        if($movimiento->save()){
          $movimiento_id=$movimiento->id;  //<---
          foreach ($request['productos'] as $producto){
            $producto_id=$producto['id'];  //<---
            $cantidad=$producto['cantidad'];//<---
            //OBTENER SALDO
            $saldo=0; //<---
            $precio=0;
            $tipo='RE'; //<--
            $items=ItemMovimientos::PorProducto($producto_id)->get();
            $cant=count($items);
            if($cant==0){
              $producto=Productos::find($producto_id);
              $precio=$producto->precio_venta;
            }else{
              $producto=Productos::find($producto_id);
              $precio=$producto->precio_venta;
              foreach($items as $item){
                //$precio+=$item->precio;
                if($item->tipo=='IN'){
                  $saldo+=$item->cantidad;
                }elseif($item->tipo=='DE'){
                  $saldo+=$item->cantidad;
                }else{
                  $saldo-=$item->cantidad;
                }
              }
            }
            $saldo=$saldo-$cantidad;
            //CREACION DE ITEMS
            $itenMovimiento=new ItemMovimientos();
            $itenMovimiento->movimientos_id=$movimiento_id;
            $itenMovimiento->productos_id=$producto_id;
            $itenMovimiento->cantidad=$cantidad;
            $itenMovimiento->saldo=$saldo;
            $itenMovimiento->precio=$precio;
            $itenMovimiento->tipo=$tipo;
            if($itenMovimiento->save()){
              $producto=Productos::find($producto_id);
              $producto->stock=$saldo;
              $producto->save();


           
              
            }
          }
          $this->generar_guia_remision($movimiento->id);
          return response()->json(array(
              'mensaje' => 1,
          ));
        }
    }

    public function show($ingreso_id){
        $movimiento = Movimientos::find($ingreso_id);

        $cliente= $movimiento->clientes->razon_social;
        $ruc= $movimiento->clientes->documento;
        $movimiento->rucProv=$ruc;
        return response()->json(array(
            'movimiento' => $movimiento,
            'cliente' => $cliente,

        ));
    }

    public function destroy($movimiento_id){
        $movimiento = Movimientos::find($movimiento_id);
        //$colaborador_id=$movimiento->colaboradores_id;
        $items=ItemMovimientos::PorMovimiento($movimiento_id)->get();
        foreach($items as $item){
          $id=$item->id;
          $productos_id=$item->productos_id;
          $cantidad=$item->cantidad;
          if($item->delete()){
            $itemProds=ItemMovimientos::PorProducto($productos_id)->get();
            $saldo=0;
            $precio=0;
            $registros=count($itemProds);
            if($registros==0){$registros=1;}
            foreach($itemProds as $itemProd){
              $precio+=$itemProd->precio;
              if($itemProd->tipo=='IN'){ $saldo+=$itemProd->cantidad; }else{ $saldo-=$itemProd->cantidad; }
              $itemMov=ItemMovimientos::find($itemProd->id);
              $itemMov->saldo=$saldo;
              $itemMov->save();
            }
            $producto=Productos::find($productos_id);
            $producto->stock=$saldo;

            $producto->save();
          }
        }
        if($movimiento->delete()){
            return response()->json(array(
                'mensaje' => 1
            ));
        }else{
            return response()->json(array(
                'mensaje' => 2
            ));
        }
    }

    //DATOS AUXILIARES
    public function obtenerTablaDetalle($movimiento_id){
        $items=ItemMovimientos::PorMovimiento($movimiento_id)->get();
        $data = [];
        foreach ($items as $item){
        $data [] = [
            'id' => $item->id,
            'descripcion' =>  $item->productos->nombre,
            'cantidad' => $item->cantidad,
            'precio_unitario' => 'S/. '.$item->precio,
            'precio_total' => 'S/. '.number_format($item->cantidad*$item->precio,2),
            'precio_final' => number_format($item->cantidad*$item->precio,2),
        ];
        }
        return response()->json(array(
            'items' => $data,
        ));
    }


    public function dataAuxiliarSalida(){
        $clientes = DB::table('clientes')
                    //->where('tipo_cliente', 'CLIENTE')
                    ->select(
                        'id',
                        'razon_social',
                        'documento',
                        'direccion'
                    )
                    ->get();

        $productos = DB::table('productos')
                    //->where()
                    ->select(
                       'id',
                       DB::raw("CONCAT_WS('',codigo,' | ', nombre, ' | STOCK: ', stock, ' | PRECIO: S/.', precio_venta) as descripcion")
                    )
                    ->get();
        return response()->json(array(
            'clientes' => $clientes,
            'productos' => $productos,
        ));
    }
    public function facturarSalidas($movimiento_id, $detraccion, $cod_bien_detraccion, $forma_pago, $dias){
      $carbon = new \Carbon\Carbon();
      $date = $carbon->now();
      $fecha_pago=$date->format('Y-m-d');

      $serie=Series::find(2);
      $correlativo_inicial=$serie->correlativo;
      $correlativo=$this->obtenerCorrelativo($correlativo_inicial);
      $serie->correlativo=$correlativo;
      $num_cod=$serie->serie.'-'.$correlativo;
      $serie->save();


      $movimiento=Movimientos::find($movimiento_id);

      //-----------------------------------
      $items=ItemMovimientos::with('productos')->PorMovimiento($movimiento_id)->get();
      $subtotal=0;
      $igv=0;
      $valor_total=0;
      foreach ($items as $item){
        $data [] = [
          'id' => $item->id,
          'descripcion' =>  $item->productos->nombre,
          'cantidad' => $item->cantidad,
          'impuesto' => number_format($item->productos->impuestos->tasa,2).' - '.$item->productos->impuestos->nombre,
          'precio_unitario' => 'S/. '.$item->precio,
          'precio_total' => 'S/. '.number_format($item->cantidad*$item->precio,2),
          'precio_final' => number_format($item->cantidad*$item->precio,2),
        ];
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
        $valor_total+=$item->cantidad*$item->precio;
      }
      //-----------------------------------

      $cliente=Clientes::find($movimiento->clientes_id);
      $factura = new Facturas();
      $factura->clientes_id = $cliente->id;
      $factura->cliente_setTipoDoc = 6;
      $factura->cliente_setNumDoc = $cliente->documento;
      $factura->cliente_setRznSocial	 = $cliente->razon_social;
      $factura->cliente_setDireccion = $cliente->direccion;
      
      $factura->setTipoDoc = '01';
      $factura->setSerie=$serie->serie;
      $factura->setCorrelativo=$correlativo;
      $factura->setFechaEmision = $fecha_pago;
      $factura->setTipoMoneda='PEN';
      //---------------------------------------
      $letras = NumeroALetras::convertir($valor_total, 'SOLES', '');
      $subtotal=$subtotal;
      $igv=$valor_total-$subtotal;
      //---------------------------------------
      if($igv==0){
        $factura->setMtoOperGravadas=NULL;
        $factura->setMtoOperExoneradas=$subtotal;
      }else{
        $factura->setMtoOperGravadas=$subtotal;
        $factura->setMtoOperExoneradas=NULL;
      }
      $factura->setMtoIGV=$igv;
      $factura->setTotalImpuestos=$igv;
      $factura->setValorVenta=$subtotal;
      $factura->setMtoImpVenta=$valor_total;
      $factura->legend_setCode='1000';
      $factura->legend_setValue=$letras;
      $factura->tipo_pago = 'EFECTIVO';
      $factura->cod_referencia = '';
      $factura->por_consumo = 0;
      if($dias != 1){
        $factura->dias = $dias;
      }
      $factura->forma_pago = $forma_pago;
      if($detraccion == '1' || $detraccion == true){
        $factura->detraccion = 1;
        $empresa = Empresas::find(1);
        $factura->setTipoOperacion='1001';  
        $factura->monto_detraccion = ($valor_total * $empresa->porcentaje_detraccion) / 100;
        $factura->cod_bien_detraccion = $cod_bien_detraccion;
      }else if($detraccion == false || $detraccion == 'false'){
        $factura->detraccion = 0;
        $factura->setTipoOperacion='0101';
      }
      if ($factura->save()) {
        $facturas_id=$factura->id;
        foreach ($items as $producto){
          $items = new Items();
          $items->facturas_id = $factura->id;
          $items->productos_id = $producto->productos->id;
          $items->setCodProducto = $producto->productos->codigo;
          $items->setUnidad='NIU';
          $items->setCantidad = $producto->cantidad;
          $items->setDescripcion = $producto->productos->nombre;
          //---------------------------------------
          $cantidad=$producto->cantidad;
          $precio=$producto->precio;
          $tasa=$producto->productos->impuestos->tasa;
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
          $items->setPorcentajeIgv='18';
          $items->setIgv=$igv*$cantidad;
          $items->setTotalImpuestos=$igv*$cantidad;
          $items->setMtoValorVenta=$subtotal*$cantidad;
          $items->setMtoValorUnitario=$subtotal;
          $items->setMtoPrecioUnitario=$precio;
          $items->setTotal = $precio;
          $items->save();
        }
        $movimiento->facturado='SI';
        $movimiento->facturas_id = $facturas_id;
        $movimiento->save();
        if($factura->detraccion == 1 && $factura->forma_pago == 'CONTADO'){
          $response=$this->generar_factura_detraccion($factura->id);  
        }else if($forma_pago == 'CREDITO' && $factura->detraccion == '0'){
          $response=$this->generar_factura_credito($facturas_id);
        }else if($factura->forma_pago == 'CREDITO' && $factura->detraccion == '1'){
          $response=$this->generar_factura_credito_detraccion($factura->id);
        }else{
          $response=$this->generar_factura($facturas_id);
        }
      }
    }

    public function generar_guia_remision($movimiento){
      
      $util = Util::getInstance();
      $movimientos = Movimientos::find($movimiento);
      //return var_dump();
      $serie=Series::find(7);
      $correlativo_inicial=$serie->correlativo;
      $correlativo=$this->obtenerCorrelativo($correlativo_inicial);
      $serie->correlativo=$correlativo;
      $serie->save();
    //$transp = new Transportist();
    //$transp->setTipoDoc('6');
        //->setNumDoc($movimientos->ruc_trans)
        //->setRznSocial($movimientos->transportista);
        //->setNroMtc('0001');
        
    $envio = new Shipment();
    $envio
        ->setCodTraslado('01') // Cat.20 - Venta
        ->setModTraslado('02') // Cat.18 - Transp. Publico
        ->setIndicadores(['SUNAT_Envio_IndicadorTrasladoVehiculoM1L'])
        ->setFecTraslado($movimientos->fecha)
        ->setPesoTotal($movimientos->peso_total)
        ->setUndPesoTotal('KGM')
    //    ->setNumBultos(2) // Solo válido para importaciones
        ->setLlegada(new Direction($movimientos->ubigeo_llegada, $movimientos->llegada))
        ->setPartida(new Direction($movimientos->ubigeo_partida, $movimientos->partida));
        //->setTransportista($transp);
        
    $despatch = new Despatch();
    $despatch->setVersion('2022')
        ->setTipoDoc('09')
        ->setSerie('T001')
        ->setCorrelativo($correlativo)
        ->setFechaEmision(new DateTime())
        ->setCompany($util->getGRECompany())
        ->setDestinatario((new Client())
            ->setTipoDoc('6')
            ->setNumDoc($movimientos->clientes->documento)
            ->setRznSocial($movimientos->clientes->razon_social))
        ->setEnvio($envio);

        $datas= array();
    foreach($movimientos->itemMovimientos as $item){
      $detail = new DespatchDetail();
      $detail->setCantidad($item->cantidad)
        ->setUnidad($item->productos->unidades->simbolo)
        ->setDescripcion($item->productos->nombre)
        ->setCodigo($item->productos->codigo);
      $datas[] = $detail;
    }

    $despatch->setDetails($datas);
        
    // Envio a SUNAT.
    $api = $util->getSeeApi();
    $res = $api->send($despatch);
    $util->writeXml($despatch, $api->getLastXml());
    if (!$res->isSuccess()) {
        echo $util->getErrorResponse($res->getError());
        return;
    }
    
    /**@var $res SummaryResult*/
    $ticket = $res->getTicket();
    
    
    echo $ticket;
    $res = $api->getStatus($ticket);
    if (!$res->isSuccess()) {
        echo $util->getErrorResponse($res->getError());
        $pdf = $util->getPdf($despatch, "CONTADO", 0, "", "", 0);
        $cdr = $res->getCdrResponse();
        $document_name = $util->writeCdr($despatch, $res->getCdrZip());
        $movimientos->document_name = $document_name;
        $movimientos->ruc_empresa = $ticket;
        $movimientos->save();
        //$util->showResponse($despatch, $cdr);
        return;
    }
    $pdf = $util->getPdf($despatch, "CONTADO", 0, "", "", 0);
    $cdr = $res->getCdrResponse();
    $document_name = $util->writeCdr($despatch, $res->getCdrZip());
    $movimientos->document_name = $document_name;
    $movimientos->ruc_empresa = $ticket;
    $util->showResponse($despatch, $cdr);
    $movimientos->save();
    // return response()->json(array(
    //   'mensaje' => 1,
    // ));
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
        $pdf = $util->getPdf($invoice, $factura->forma_pago, $factura->detraccion, '', $guia, '');
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
      ->setMtoImpVenta($factura->setMtoImpVenta)
      ->setSubTotal($factura->setMtoImpVenta)
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
      $movimientos = Movimientos::where('facturas_id', $factura_id)->where('tipo_doc', 'GUIA DE REMISION')->first();
      if($movimientos != null){
        $guia = $movimientos->num_doc;
      }else{
        $guia = '';
      }
      $pdf = $util->getPdf($invoice, $factura->forma_pago, $factura->detraccion,'', $guia, '');
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
      ->setFechaEmision(new \DateTime($factura->setFechaEmision))
      ->setTipoMoneda($factura->setTipoMoneda)
      ->setClient($client)
       ->setMtoOperGravadas($factura->setMtoOperGravadas)
      ->setMtoOperExoneradas($factura->setMtoOperExoneradas)
      ->setMtoIGV($factura->setMtoIGV)
      ->setTotalImpuestos($factura->setTotalImpuestos)
      ->setValorVenta($factura->setValorVenta)
      ->setMtoImpVenta($factura->setMtoImpVenta)
      ->setSubTotal($factura->setMtoImpVenta)
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
      $movimientos = Movimientos::where('facturas_id', $factura_id)->where('tipo_doc', 'GUIA DE REMISION')->first();
      if($movimientos != null){
        $guia = $movimientos->num_doc;
      }else{
        $guia = '';
      }
      $dias_factura = Facturas::find($factura_id);

      $pdf = $util->getPdf($invoice, $factura->forma_pago, $factura, $dias_factura->dias_credito, $guia, '');
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
}
