<?php

namespace App\Http\Controllers\Api\web\ventas;

use Illuminate\Http\Request;
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
use App\Models\Movimientos;
use App\Models\Items;
use App\Models\Series;

//paginacion fractal
use App\Transformers\FacturasTransformer;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;

class NotasController extends Controller
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
                                     ->where('setTipoDoc','07')
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
        return response()->json(array(
            'factura' => $factura,
        ));
    }

    public function generar_pdf_nota($factura_id){
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
          ->setTipDocAfectado($factura->setTipDocAfectado)//01 FACTURA ||03 BOLETA DE VENTA ||07 NOTA DE CREDITO
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

          $legend = new Legend();
          $legend->setCode($factura->legend_setCode)
          ->setValue($factura->legend_setValue);
          $note->setDetails($datas)
          ->setLegends([$legend]);
            $movimientos = Movimientos::where('facturas_id', $factura_id)->where('tipo_doc', 'GUIA DE REMISION')->first();
            if($movimientos != null){
                $guia = $movimientos->num_doc;
            }else{
                $guia = '';
            }
          try {
            $pdf = $util->getPdf($note, 'CONTADO', 0, $guia);
            return response()->json(array(
                'nombrePDF' => $pdf
            ));
          } catch (Exception $e) {
              var_dump($e);
          }
        //}*/
    }

    public function downloadNotaPDF($nombrePDF){
        //$file= public_path(). "/greenter/files/".$nombrePDF.".pdf";
        $file=  "/home/softgasa/public_html/apisofer/greenter/files/".$nombrePDF.".pdf";

        $headers = array(
            'Content-Type: application/pdf',
        );
        return Response::download($file, $nombrePDF.'.pdf', $headers);
    }
}
