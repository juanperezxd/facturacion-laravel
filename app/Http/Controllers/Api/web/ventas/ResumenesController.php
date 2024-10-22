<?php

namespace App\Http\Controllers\Api\web\ventas;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use App\Models\Facturas;
use App\Custom\Util;
use Greenter\Model\Client\Client;
use Greenter\Model\Company\Company;
use Greenter\Model\Company\Address;
use Greenter\Model\Sale\Invoice;
use Greenter\Model\Sale\SaleDetail;
use Greenter\Model\Sale\Legend;
use Greenter\Model\Sale\Document;
use Greenter\Ws\Services\SunatEndpoints;
use Greenter\Data\StoreTrait;
use Greenter\Model\DocumentInterface;
use Greenter\Model\Response\CdrResponse;
use Greenter\Report\HtmlReport;
use Greenter\Report\PdfReport;
use Greenter\Model\Summary\Summary;
use Greenter\Model\Summary\SummaryDetail;
use Greenter\Model\Summary\SummaryPerception;
use Yajra\Datatables\Facades\Datatables;
//paginacion fractal
use App\Transformers\FacturasTransformer;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;


class ResumenesController extends Controller
{

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
        //paginacion
        $facturasPaginator = Facturas::where('id', 'like', '%' . $id . '%')
                                    ->where('cliente_setTipoDoc','99')
                                    ->orderby($row,$order)
                                    ->paginate(10);
        $facturas =  new Collection($facturasPaginator->items(), $this->facturasTransformer);
        $facturas->setPaginator(new IlluminatePaginatorAdapter($facturasPaginator));
        $facturas = $this->fractal->createData($facturas);
        return $facturas->toArray();
    }

    public function show($factura_id){
        $resumen=Facturas::find($factura_id);
        return response()->json(array(
            'resumen' => $resumen,
        ));
    }

    public function conseguirBoletas($fecha_resumen){
        $data= [];


        $boletas = Facturas::where('ticket',null)
                    ->whereDate('setFechaEmision', '=', $fecha_resumen)->where('setSerie','like','B%')->where('ticket',null)
                    ->get();
        foreach ($boletas as $boleta){
          $data [] = [
            'documento' => $boleta->cliente_setNumDoc,
            'razon_social' => $boleta->cliente_setRznSocial,
            'serie' => $boleta->setSerie.'-'.$boleta->setCorrelativo,
            'fecha_emision' => $boleta->setFechaEmision,
            'valor_venta' => $boleta->setMtoOperGravadas,
            'igv' => $boleta->setMtoIGV,
            'total' => $boleta->setMtoImpVenta,
          ];
        }
        return response()->json(array(
          'boletas' => $data,
        ));
    }

    public function generarResumen($fecha_resumen){
        $carbon = new \Carbon\Carbon();
        $date = $carbon::now();
        $date = $date->format('Y-m-d');

        $faturas= Facturas::whereDate('created_at', '=', $carbon::today()->toDateString())->where('cliente_setTipoDoc','99')->get();
        $envios=count($faturas);
        $siguiente=$envios+1;
        $correltivo='00'.$siguiente;

        $util = Util::getInstance();
        $boletas=Facturas::BuscarPendientesPorDiaResumen($fecha_resumen)->get();

        $items= array();

        foreach($boletas as $boleta){
          if($boleta->setTipoDoc=='03'){
            $setSerieNro=$boleta->setSerie.'-'.$boleta->setCorrelativo;
            $detiail1 = new SummaryDetail();
            $detiail1->setTipoDoc('03')
            ->setSerieNro($setSerieNro)
            ->setEstado('1')
            ->setClienteTipo('1')
            ->setClienteNro($boleta->cliente_setNumDoc)
            ->setTotal($boleta->setMtoImpVenta)
            ->setMtoOperGravadas($boleta->setMtoOperGravadas)
            ->setMtoIGV($boleta->setMtoIGV);
            $items[]=$detiail1;
          }else if($boleta->setTipoDoc=='07' || $boleta->setTipoDoc=='08'){
            $setSerieNro=$boleta->setSerie.'-'.$boleta->setCorrelativo;
            $detiail1 = new SummaryDetail();
            $detiail1->setTipoDoc($boleta->setTipoDoc)
            ->setSerieNro($setSerieNro)
            ->setDocReferencia((new Document())
              ->setTipoDoc($boleta->setTipDocAfectado) //TIPO DE DOCUMENTO DE LA BOLETA ANULADA
              ->setNroDoc($boleta->setNumDocfectado)  //NUMERO DE DOCUMENTO DE LA BOLETA ANULADA
            )
            ->setEstado('1')
            ->setClienteTipo('1')
            ->setClienteNro($boleta->cliente_setNumDoc)
            ->setTotal($boleta->setMtoImpVenta)
            ->setMtoOperGravadas($boleta->setMtoOperGravadas)
            ->setMtoIGV($boleta->setMtoIGV);
            $items[]=$detiail1;
          }
        }
        $sum = new Summary();
        $sum->setFecGeneracion(new \DateTime())
            ->setFecResumen(new \DateTime($fecha_resumen))
            ->setCorrelativo($correltivo)
            ->setCompany($util->getCompany())
            ->setDetails($items);
        //PREPARACION DE ENVIO CON LOS ITEMS CREADOS
        $sum->setFecGeneracion(new \DateTime());
        $sum->setFecResumen(new \DateTime($fecha_resumen));

        // Envio a SUNAT.
        //$see = $util->getSee(SunatEndpoints::FE_BETA);
        $see = $util->getSee(SunatEndpoints::FE_PRODUCCION);
        $res = $see->send($sum);
        $response=$util->writeXml($sum, $see->getFactory()->getLastXml());
        if ($res->isSuccess()){
          $ticket = $res->getTicket();
          $resumen=new Facturas();
          $resumen->document_name=$response;
          $resumen->cliente_setTipoDoc='99';
          $resumen->setCorrelativo=$correltivo;
          $resumen->setFechaEmision=$fecha_resumen;
          $resumen->setMtoOperGravadas=count($boletas);
          $resumen->legend_setValue='CREACION DE RESUMEN DIARIO: '.$response;
          $resumen->estado='1';
          $resumen->ticket=$ticket;
          if($resumen->save()){
            foreach($boletas as $boleta){
              $bolfind=Facturas::find($boleta->id);
              $bolfind->estadoDesc='ENVIADA';
              $bolfind->ticket=$ticket;
              $bolfind->save();
            }
          }
          $result = $see->getStatus($ticket);
          $result->getCdrResponse();
          if ($result->isSuccess()) {
            $cdr = $result->getCdrResponse();
            $util->writeCdr($sum, $result->getCdrZip());
            $response=$util->showResponse($sum, $cdr);
            $util->getPdfResumen($sum);
              return response()->json([
                "mensaje" => 'SUCCESS,'.$ticket
              ]);
          } else {
            $util->getErrorResponse($result->getError());
            return response()->json([
              "mensaje" => 'ERROR,'.$util->getErrorResponse($result->getError())
            ]);
          }
        }else{
          $util->getErrorResponse($res->getError());
          return response()->json([
            "mensaje" => 'ERROR,'.$util->getErrorResponse($res->getError())
          ]);
        }
    }
    function getName(){
        return $this->company->getRuc().'-'.$this->getXmlId();
    }

    public function conseguirBoletasResumen($factura_id){
        $factura=Facturas::find($factura_id);
        $ticket=$factura->ticket;
        $boletas=Facturas::where('ticket',$ticket)->where('cliente_setTipoDoc','<>','99')->where('cliente_setTipoDoc','<>','01')->get();
        $data = [];
        foreach($boletas as $boleta){
          if($boleta->setTipoDoc=='07'){ $tipo='NOTA DE CREDITO'; }else if($boleta->setTipoDoc=='03'){ $tipo='BOLETA'; }
          $data [] = [
            'documento' => $boleta->cliente_setNumDoc,
            'razon_social' => $boleta->cliente_setRznSocial,
            'serie' => $boleta->setSerie.'-'.$boleta->setCorrelativo,
            'fecha' => $boleta->setFechaEmision,
            'valor' => $boleta->setMtoOperGravadas,
            'igv' => $boleta->setMtoIGV,
            'total' => $boleta->setMtoImpVenta,
            'estado' => $boleta->estado.'-'.$boleta->estadoDesc,
            'tipo' => $tipo,
          ];


        }
        return response()->json([
          "boletas" => $data
        ]);
    }
}
