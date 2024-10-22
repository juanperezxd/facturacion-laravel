<?php
namespace App\Http\Controllers\api\web\contabilidad;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Greenter\Model\Client\Client;
use Greenter\Model\Company\Company;
use Greenter\Model\Company\Address;
use Greenter\Model\Sale\Invoice;
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
use NumeroALetras;
use App\Models\Facturas;
use App\Models\Items;
use App\Models\Contratos;
use App\Models\Series;

class FacturadorController extends Controller{
  public function emitirComp($contrato_id){
      $carbon = new \Carbon\Carbon();
      $date = $carbon::now();
      $hoy=$date->format('Y-m-d');
      $contrato=Contratos::find($contrato_id);
      $direccion=$this->obtenerDireccion($contrato_id);

      $precio=$contrato->precio;
      $subtotal=number_format($precio/1.18,2);
      $igv=number_format($precio-$subtotal,2);

      $factura=Facturas::BuscarContrato($contrato_id)->get();
      $tipo_doc='BOLETA DE VENTA';
      $letras = NumeroALetras::convertir($precio, 'SOLES', '');
      if($tipo_doc=='BOLETA DE VENTA'){
        if(count($factura)==0){
          //obtener correlativo
          $serie=Series::find(1);
          $correlativo_inicial=$serie->correlativo;
          $correlativo=$this->obtenerCorrelativo($correlativo_inicial);

          $serie->correlativo=$correlativo;
          $num_cod=$serie->serie.'-'.$correlativo;
          if($serie->save()){
            //------------
            //guardar numero de serie en el pago.
            $contrato->boleta=$num_cod;
            $contrato->save();
          }
          $factura = new Facturas();
          $factura->contratos_id=$contrato_id;
          $factura->cliente_setTipoDoc=1;
          $factura->cliente_setNumDoc=$contrato->dni;
          $factura->cliente_setRznSocial=$contrato->nombres.' '.$contrato->apellidos;
          $factura->cliente_setDireccion=$direccion;
          $factura->setTipoOperacion='0101';
          $factura->setTipoDoc='03';
          $factura->setSerie=$serie->serie;
          $factura->setCorrelativo=$correlativo;
          $factura->setFechaEmision=$hoy;
          $factura->setTipoMoneda='PEN';
          $factura->setMtoOperGravadas=$subtotal;     //MONTO SIN IGV
          $factura->setMtoIGV=$igv;
          $factura->setTotalImpuestos=$igv;        //IGV DE LA VENTA
          $factura->setValorVenta=$subtotal;  //MONTO SIN IGV
          $factura->setMtoImpVenta=$precio; //TOTAL DE FACTURA
          $factura->legend_setCode=1000;
          $factura->legend_setValue=$letras;
          $factura->estado='1';   //0 = SIN PROCESAR

          if($factura->save()){
            $item=new Items();
            $item->facturas_id=$factura->id;
            $item->setCodProducto='INS';
            $item->setUnidad='NIU';
            $item->setCantidad=1;
            $item->setDescripcion='INSTALACION DE GAS DOMICILIARIO, CUENTA CONTRATO ('.$contrato->cuenta_contrato.')';
            $item->setMtoBaseIgv=$subtotal;
            $item->setPorcentajeIgv=18;
            $item->setIgv=$igv;
            $item->setTipAfeIgv=10;
            $item->setTotalImpuestos=$igv;
            $item->setMtoValorVenta=$subtotal;
            $item->setMtoValorUnitario=$subtotal;
            $item->setMtoPrecioUnitario=$precio;
            $item->save();
          }
          return $this->generar_boleta($contrato_id);
        }else{
          return $this->generar_boleta($contrato_id);
        }
      }
    }
    function generar_boleta($contrato_id){
      //OBTENCION DE DATOS.
      $factura=Facturas::BuscarContrato($contrato_id)->first();
      $factura_id=$factura->id;
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
      ->setTipoDoc($factura->setTipoDoc)//
      ->setSerie($factura->setSerie)//
      ->setCorrelativo(intval($factura->setCorrelativo))
      ->setFechaEmision(new \DateTime())
      ->setTipoMoneda($factura->setTipoMoneda)
      ->setClient($client)
      ->setMtoOperGravadas($factura->setMtoOperGravadas)
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


    }
    function generar_pdf($contrato_id){
      //OBTENCION DE DATOS.
      $factura=Facturas::BuscarContrato($contrato_id)->first();
      //$factura=Facturas::Buscarpago($pago_id)->first();
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
      ->setFechaEmision(new \DateTime())
      ->setTipoMoneda($factura->setTipoMoneda)
      ->setClient($client)
      ->setMtoOperGravadas($factura->setMtoOperGravadas)
      ->setMtoIGV($factura->setMtoIGV)
      ->setTotalImpuestos($factura->setTotalImpuestos)
      ->setValorVenta($factura->setValorVenta)
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
        ->setPorcentajeIgv($item->setPorcentajeIgv) // 18%
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
      try {
        $factura=Facturas::find($factura_id);
        $factura->document_name=$invoice->getName();
        $factura->estado=1;
        $factura->estadoDesc='PROCESADA';
        $factura->save();
        $pdf = $util->getPdf($invoice);
        return response()->json(array(
            'nombrePDF' => $pdf
        ));
      } catch (Exception $e) {
        //  var_dump($e);
      }
    }

    public function downloadPDF($pdf){
      //$file= public_path(). "/greenter/files/".$pdf.".pdf";
      $file=  "/home/softgasa/public_html/apisofer/greenter/files/".$pdf.".pdf";
      $headers = array(
        'Content-Type: application/pdf',
      );
      return Response::download($file, $pdf.'.pdf', $headers);
    }

    public function downloadXML($xml){
      //$file= public_path(). "/greenter/files/".$xml.".xml";
      $file=  "/home/softgasa/public_html/apisofer/greenter/files/".$xml.".xml";
      //$file= url('/'). "/greenter/files/".$xml.".xml";

      return Response::download($file, $xml.'.xml');
    }
    public function downloadCRD($crd){
      $cdr='R-'.$crd;
      //$file= public_path(). "/greenter/files/".$cdr.".zip";
      $file=  "/home/softgasa/public_html/apisofer/greenter/files/".$cdr.".zip";
      $headers = array(
        'Content-Type: application/zip',
      );
      return Response::download($file, $cdr.'.zip', $headers);
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
    function obtenerDireccion($contrato_id){
      $contrato=Contratos::find($contrato_id);
      $num_puerta = $contrato->numero;
      $direccion = '';
      //sin numero de puerta1
      if ($num_puerta == 'S/N' or $num_puerta == 's/N' or $num_puerta == 's/n' or $num_puerta == 'S/n'){
        if ($contrato->nombre_via == 'S/N' or $contrato->nombre_via == 's/N' or $contrato->nombre_via == 's/n' or $contrato->nombre_via == 'S/n'  ) {
          if ($contrato->manzana == '') {
            if ($contrato->lote == '') {
              $direccion = $contrato->tipo_via.' S/N '.' S/N '.$contrato->conjunto_vivienda ;
            }else {
              $direccion = $contrato->tipo_via.' S/N '.' S/N '.'LT'.$contrato->lote.' '.$contrato->conjunto_vivienda;
            }
          }else{
            if ($contrato->lote == '') {

              $direccion = $contrato->tipo_via.' S/N '.' S/N '.'MZ-'.$contrato->manzana.' '.$contrato->conjunto_vivienda;

            }else {
              $direccion = $contrato->tipo_via.' S/N '.' S/N '.'MZ-'.$contrato->manzana.' LT-'.$contrato->lote.' '.$contrato->conjunto_vivienda;
            }
          }
        }else{
          if ($contrato->manzana == '') {
            if ($contrato->lote == '') {
              $direccion = $contrato->tipo_via.' '.$contrato->nombre_via.' S/N '.$contrato->conjunto_vivienda ;
            }else {
              $direccion = $contrato->tipo_via.' '.$contrato->nombre_via.' S/N '.' LT-'.$contrato->lote.' '.$contrato->conjunto_vivienda;
            }
          }else{
            if ($contrato->lote == '') {

              $direccion = $contrato->tipo_via.' '.$contrato->nombre_via.' S/N '.'MZ-'.$contrato->manzana.' '.$contrato->conjunto_vivienda;

            }else {
              $direccion = $contrato->tipo_via.' '.$contrato->nombre_via.' S/N '.'MZ-'.$contrato->manzana.' '.'LT-'.$contrato->lote.' '.$contrato->conjunto_vivienda;
            }
          }
        }
      }else{
        //con numero de puerta
        if ($contrato->nombre_via == 'S/N' or $contrato->nombre_via == 's/N' or $contrato->nombre_via == 's/n' or $contrato->nombre_via == 'S/n'  ) {
          if ($contrato->manzana == '') {
            if ($contrato->lote == '') {
              $direccion = $contrato->tipo_via.' S/N '.$contrato->numero.' '.$contrato->conjunto_vivienda ;
            }else {
              $direccion = $contrato->tipo_via.' S/N '.$contrato->numero.' '.'LT-'.$contrato->lote.' '.$contrato->conjunto_vivienda;
            }
          }else{
            if ($contrato->lote == '') {
              $direccion = $contrato->tipo_via.' S/N '.$contrato->numero.' '.'MZ-'.$contrato->manzana.' '.$contrato->conjunto_vivienda;
            }else {
              $direccion = $contrato->tipo_via.' S/N '.$contrato->numero.' '.'MZ'.$contrato->manzana.' '.'LT-'.$contrato->lote.' '.$contrato->conjunto_vivienda;
            }
          }
        }else{
          if ($contrato->manzana == '') {
            if ($contrato->lote == '') {
              $direccion = $contrato->tipo_via.' '.$contrato->nombre_via.' '.$contrato->numero.' '.$contrato->conjunto_vivienda ;
            }else {
              $direccion = $contrato->tipo_via.' '.$contrato->nombre_via.' '.$contrato->numero.' '.'LT-'.$contrato->lote.' '.$contrato->conjunto_vivienda;
            }
          }else{
            if ($contrato->lote == '') {
              $direccion = $contrato->tipo_via.' '.$contrato->nombre_via.' '.$contrato->numero.' '.'MZ-'.$contrato->manzana.' '.$contrato->conjunto_vivienda;
            }else {
              $direccion = $contrato->tipo_via.' '.$contrato->nombre_via.' '.$contrato->numero.' '.'MZ-'.$contrato->manzana.' '.'LT-'.$contrato->lote.' '.$contrato->conjunto_vivienda;
            }
          }
        }
      }
      return $direccion;
    }

}
