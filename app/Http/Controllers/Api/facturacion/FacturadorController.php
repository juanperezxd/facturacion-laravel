<?php

namespace App\Http\Controllers\Api\facturacion;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
//MODULO FACTURADOR

use Greenter\XMLSecLibs\Certificate\X509Certificate;
use Greenter\XMLSecLibs\Certificate\X509ContentType;
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

use Datetime;

use App\Custom\Util;


class FacturadorController extends Controller
{
    public function prueba(){

        $util = Util::getInstance();
        $see = $util->conseguirDatos();

                // Cliente
        $client = new Client();
        $client->setTipoDoc('1')
            ->setNumDoc('20203030')
            ->setRznSocial('PERSON 1');


        $invoice = (new Invoice())
            ->setUblVersion('2.1')
            ->setTipoOperacion('0101') // Catalog. 51
            ->setTipoDoc('03')
            ->setSerie('B001')
            ->setCorrelativo('1')
            ->setFechaEmision(new DateTime())
            ->setTipoMoneda('PEN')
            ->setClient($client)
            ->setMtoOperGravadas(100.00)
            ->setMtoIGV(18.00)
            ->setTotalImpuestos(18.00)
            ->setValorVenta(100.00)
            ->setMtoImpVenta(118.00)
            ->setCompany($util->getCompany());

        $item = (new SaleDetail())
            ->setCodProducto('P001')
            ->setUnidad('NIU')
            ->setCantidad(2)
            ->setDescripcion('PRODUCTO 1')
            ->setMtoBaseIgv(100)
            ->setPorcentajeIgv(18.00) // 18%
            ->setIgv(18.00)
            ->setTipAfeIgv('10')
            ->setTotalImpuestos(18.00)
            ->setMtoValorVenta(100.00)
            ->setMtoValorUnitario(50.00)
            ->setMtoPrecioUnitario(56.00);

            $legend = (new Legend())
    ->setCode('1000')
    ->setValue('SON DOSCIENTOS TREINTA Y SEIS CON 00/100 SOLES');


            $invoice->setDetails([$item])
                    ->setLegends([$legend]);

            $result = $see->send($invoice);

            //var_dump($result);

        if ($result->isSuccess()) {
            return response()->json(array(
                'resultado' => $result->getCdrResponse()->getDescription(),
            ));
        } else {
            return response()->json(array(
                'error' => $result->getError(),
            ));
        }

    }



    public function emitirComp($contrato_id){
        $carbon = new \Carbon\Carbon();
        $date = $carbon::now();
        $hoy=$date->format('Y-m-d');
        $contrato=Contrato::find($contrato_id);
        $convenio=Convenio::BuscarContrato($contrato_id)->first();
        $hojaUnica=HojaUnica::BuscarContrato($contrato_id)->get();
        $direccion=$this->obtenerDireccion($contrato_id);

        $precio=$convenio->precio;
        $subtotal=number_format($precio/1.18,2);
        $igv=number_format($precio-$subtotal,2);

        $factura=Facturas::BuscarContrato($contrato_id)->get();
        $tipo_doc='BOLETA DE VENTA';
        $letras = NumeroALetras::convertir($precio, 'SOLES', '');
        if($tipo_doc=='BOLETA DE VENTA'){
          if(count($factura)==0){
            //obtener correlativo
            $serie=Series::find($contrato->serie);
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
            $factura->contrato_id=$contrato_id;
            $factura->cliente_setTipoDoc=1;
            $factura->cliente_setNumDoc=$convenio->dni;
            $factura->cliente_setRznSocial=$contrato->razon_social;
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
            $factura->estado='SIN PROCESAR';   //0 = SIN PROCESAR
            if($factura->save()){
              $item=new Items();
              $item->facturas_id=$factura->id;
              $item->setCodProducto='INS';
              $item->setUnidad='NIU';
              $item->setCantidad=1;
              $item->setDescripcion='INSTALACION DE GAS DOMICILIARIO';
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
        }else if($tipo_doc=='FACTURA'){

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
        ->setTipoDoc($factura->setTipoDoc)
        ->setSerie($factura->setSerie)
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
        //Leyenda
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
        $cdr = $res->getCdrResponse();


        if ($res->isSuccess()) {
          /**@var $res \Greenter\Model\Response\BillResult*/
          $cdr = $res->getCdrResponse();

          $document_name=$util->writeCdr($invoice, $res->getCdrZip());
          $code=$cdr->getCode();
          $descripcion=$cdr->getDescription();
          $editFactura=Facturas::find($factura_id);
          $editFactura->estado=$code;
          $editFactura->estadoDesc=$descripcion;
          $editFactura->document_name=$document_name;
          $editFactura->save();
          //echo $util->getResponseFromCdr($cdr);
          echo $code;
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

    //FUNCION PARA CONVERTIR CERTIFICADO
    public function convertir_cert(){
      $fullUrl = $_SERVER['DOCUMENT_ROOT'];
      $pfx = file_get_contents($fullUrl.'/greenter/certs/20604963231.pfx');
      $password = 'sofiarocha04';
      $certificate = new X509Certificate($pfx, $password);
      $pem = $certificate->export(X509ContentType::PEM);
      file_put_contents('20604963231.pem', $pem);
    }


}
