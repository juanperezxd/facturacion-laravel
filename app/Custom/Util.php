<?php namespace App\Custom;
//use Greenter\Data\StoreTrait;
use Greenter\Model\DocumentInterface;
use Greenter\Model\Response\CdrResponse;
use Greenter\Report\HtmlReport;
use Greenter\Report\PdfReport;
use Greenter\Ws\Services\SunatEndpoints;
use Greenter\See;
use Greenter\Model\Company\Address;
use Greenter\Model\Company\Company;
use mikehaertl\wkhtmlto\Pdf;
use Dompdf\Dompdf;
use App\Models\Empresas;
use App\Models\Facturas;
use App\Models\Movimientos;
use DateTime;

class Util{
  //use StoreTrait;
  private static $current;
  private function __construct(){
  }
  public function conseguirDatos(){
    //$see->setCredentials('20601828252MEGAREDE', 'Ingenieria10');
    $fullUrl = $_SERVER['DOCUMENT_ROOT'];
    $see = new \Greenter\See();
     $see->setService(SunatEndpoints::FE_BETA);
     $see->setCertificate(file_get_contents($fullUrl.'/greenter/certs/certificate.pem'));
     $see->setCredentials('20000000001MODDATOS', 'moddatos');

    return $see;
  }
  public function getErrorResponse(\Greenter\Model\Response\Error $error){
      $result = <<<HTML
      <h2 class="text-danger">Error:</h2><br>
      <b>Código:</b>{$error->getCode()}<br>
      <b>Descripción:</b>{$error->getMessage()}<br>
HTML;
      return $result;
  }

  public function getCompany(){
      return (new Company())
      ->setRuc('20604963231')
      ->setNombreComercial('MULTISERVICIOS SOFER E.I.R.L.')
      ->setRazonSocial('MULTISERVICIOS SOFER E.I.R.L.')
      ->setAddress((new Address())
          ->setUbigueo('110101')
          ->setDistrito('CERCADO DE ICA')
          ->setProvincia('ICA')
          ->setDepartamento('ICA')
          ->setUrbanizacion('-')
          ->setCodLocal('0000')
          ->setDireccion('Urb. Santo Domingo De Guzman J 39 (Pro. Luis Geronimo de Cabrera) Ref: Frente a ICASUR - Ica'));
  }


  public static function getInstance(){
    if (!self::$current instanceof self){
      self::$current = new self();
    }
    return self::$current;
  }
  public function getSee($endpoint){
    $fullUrl = $_SERVER['DOCUMENT_ROOT'];
    $see = new See();
    $see->setService($endpoint);
    //$see->setCodeProvider(new XmlErrorCodeProvider());
    $see->setCertificate(file_get_contents($fullUrl.'/greenter/certs/certificate.pem'));
    $see->setCredentials('20000000001MODDATOS', 'moddatos');

    return $see;
  }
  public function showResponse(DocumentInterface $document, CdrResponse $cdr){
    $filename = $document->getName();
//    require __DIR__.'/../views/response.php';
}

public function getSeeApi()
    {
      $fullUrl = $_SERVER['DOCUMENT_ROOT'];
        $api = new \Greenter\Api([
            'auth' => 'https://gre-test.nubefact.com/v1',
            'cpe' => 'https://gre-test.nubefact.com/v1',
        ]);
        $certificate = file_get_contents($fullUrl.'/greenter/certs/certificate.pem');
        if ($certificate === false) {
            throw new Exception('No se pudo cargar el certificado');
        }
        return $api->setBuilderOptions([
                'strict_variables' => true,
                'optimizations' => 0,
                'debug' => true,
                'cache' => false,
            ])
            ->setApiCredentials('test-85e5b0ae-255c-4891-a595-0b98c65c9854', 'test-Hty/M6QshYvPgItX2P0+Kw==')
            ->setClaveSOL('20161515648', 'MODDATOS', 'MODDATOS')
            ->setCertificate($certificate);
       
    }

    public function getGRECompany(): \Greenter\Model\Company\Company
    {
        return (new \Greenter\Model\Company\Company())
            ->setRuc('20604963231')
            ->setRazonSocial('MULTISERVICIOS SOFER E.I.R.L.')
            ->setAddress((new Address())
                ->setUbigueo('110101')
                ->setDistrito('CERCADO DE ICA')
                ->setProvincia('ICA')
                ->setDepartamento('ICA')
                ->setUrbanizacion('-')
                ->setCodLocal('0000')
                ->setDireccion('Urb. Santo Domingo De Guzman J 39 (Pro. Luis Geronimo de Cabrera) Ref: Frente a ICASUR - Ica'));
            ;
    }

  public function getResponseFromCdr(CdrResponse $cdr){
    $result = <<<HTML
    <h2>Respuesta SUNAT:</h2><br>
    <b>ID:</b> {$cdr->getId()}<br>
    <b>CODE:</b>{$cdr->getCode()}<br>
    <b>DESCRIPTION:</b>{$cdr->getDescription()}<br>
HTML;
    return $result;
  }

  public function writeXml(DocumentInterface $document, $xml){
    $this->writeFile($document->getName().'.xml', $xml);
    return $document->getName();
  }
  public function writeCdr(DocumentInterface $document, $zip){
    $name='R-'.$document->getName().'.zip';
    $this->writeFile($name, $zip);
    return $document->getName();
  }
  public function writeFile($filenam, $content){
    $fullUrl = $_SERVER['DOCUMENT_ROOT'];
    if (getenv('GREENTER_NO_FILES')){
      return;
    }
    file_put_contents($fullUrl.'/greenter/files/'.$filenam, $content);
  }
  public function getPdf(DocumentInterface $document, $forma_pago, $detraccion, $guia, $nroguia, $detra){
    $fullUrl = $_SERVER['DOCUMENT_ROOT'];
    $html = new HtmlReport('', [
      'cache' => $fullUrl.'/greenter/cache',
      'strict_variables' => true,
    ]);
    $template = $this->getTemplate($document);
    if ($template) {
      $html->setTemplate($template);
    }
    $render = new PdfReport($html);
    $render->setOptions( [
      'no-outline',
      'viewport-size' => '1280x1024',
      'page-width' => '21cm',
      'page-height' => '29.7cm',
      'footer-html' => $fullUrl.'/greenter/resources/footer.html',
    ]);
    $binPath = self::getPathBin();
    if (file_exists($binPath)) {
      $render->setBinPath($binPath);
    }
    $hash = $this->getHash($document);
    if($forma_pago == 'CREDITO' && $detra == 1){ 
      $params = self::getParametersPdfCreditoDetraccion($forma_pago, $guia, $detraccion->dias, $detraccion->dias_credito, $nroguia);
    }else if($forma_pago == 'CREDITO'){ 
      $params = self::getParametersPdfCredito($forma_pago, $guia, $detraccion->dias, $detraccion->dias_credito, $nroguia);
    }else if($detraccion == 1 && $forma_pago == 'CONTADO'){
      $params = self::getParametersPdfDetraccion($forma_pago, $nroguia);
    }else if($forma_pago == 'CONTADO' && $detraccion == 0){
      $params = self::getParametersPdf($forma_pago, $nroguia);
    }
    $nombre = $document->getName();
    $url =  '<div>XML: <a href="';
    $url .= '/greenter/files/'. $nombre . '.xml';
    $url .= '">Ver XML</a></div> <br>';


    $url .=  '<div>CDR: <a href="';
    $url .= '/greenter/files/'. 'R-' . $nombre . '.zip';
    $url .= '">Descargar CDR</a></div> <br>';
    $params['system']['hash'] = $hash;
    $params['user']['footer'] = $url;
    $pdf = $render->render($document, $params);
    if ($pdf === false) {
      //    $error = $render->getExporter()->getError();
      //  echo 'Error: '.$error;
      //    exit();
    }
    // Write html
    $this->writeFile($document->getName().'.html', $render->getHtml());
    $dompdf = new DOMPDF();
    $dompdf->load_html( file_get_contents( $fullUrl.'/greenter/files/'.$document->getName().'.html' ) );
    $dompdf->render();
    // $dompdf->stream($fullUrl.'/greenter/files/'.$document->getName().'.pdf');
    file_put_contents($fullUrl.'/greenter/files/'.$document->getName().'.pdf', $dompdf->output());
    return $document->getName();
  }

  public function getPdfResumen(DocumentInterface $document){
    $fullUrl = $_SERVER['DOCUMENT_ROOT'];
    $html = new HtmlReport('', [
      'cache' => $fullUrl.'/greenter/cache',
      'strict_variables' => true,
    ]);
    $template = $this->getTemplate($document);
    if ($template) {
      $html->setTemplate($template);
    }
    $render = new PdfReport($html);
    $render->setOptions( [
      'no-outline',
      'viewport-size' => '1280x1024',
      'page-width' => '21cm',
      'page-height' => '29.7cm',
      'footer-html' => $fullUrl.'/greenter/resources/footer.html',
    ]);
    $binPath = self::getPathBin();
    if (file_exists($binPath)) {
      $render->setBinPath($binPath);
    }
    $hash = $this->getHash($document);
    $params = self::getParametersPdf("CONTADO", "");
    $params['system']['hash'] = $hash;
    $params['user']['footer'] = '<div>consulte en <a href="https://github.com/giansalex/sufel">sufel.com</a></div>';
    $pdf = $render->render($document, $params);
    if ($pdf === false) {
      //    $error = $render->getExporter()->getError();
      //  echo 'Error: '.$error;
      //    exit();
    }
    // Write html
    $this->writeFile($document->getName().'.html', $render->getHtml());
    $dompdf = new DOMPDF();
    $dompdf->load_html( file_get_contents( $fullUrl.'/greenter/files/'.$document->getName().'.html' ) );
    $dompdf->render();
    //  $dompdf->stream($fullUrl.'/greenter/files/'.$document->getName().'.pdf');
    file_put_contents($fullUrl.'/greenter/files/'.$document->getName().'.pdf', $dompdf->output());
    return $document->getName();
  }
    public static function generator($item, $count)
    {
        $items = [];

        for ($i = 0; $i < $count; $i++) {
            $items[] = $item;
        }

        return $items;
    }

    public function showPdf($content, $filename){
        $this->writeFile($filename, $content);
        header('Content-type: application/pdf');
        header('Content-Disposition: inline; filename="' . $filename . '"');
        header('Content-Transfer-Encoding: binary');
    }

    public static function getPathBin()
    {
        $fullUrl = $_SERVER['DOCUMENT_ROOT'];
        $path = $fullUrl.'/vendor/bin/wkhtmltopdf';
        if (self::isWindows()) {
            $path .= '.exe';
        }

        return $path;
    }

    public static function isWindows()
    {
        return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
    }

    public static function inPath($command) {
        $whereIsCommand = self::isWindows() ? 'where' : 'which';

        $process = proc_open(
            "$whereIsCommand $command",
            array(
                0 => array("pipe", "r"), //STDIN
                1 => array("pipe", "w"), //STDOUT
                2 => array("pipe", "w"), //STDERR
            ),
            $pipes
        );
        if ($process !== false) {
            $stdout = stream_get_contents($pipes[1]);
            stream_get_contents($pipes[2]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            proc_close($process);

            return $stdout != '';
        }

        return false;
    }

    private function getTemplate($document)
    {
        $className = get_class($document);

        switch ($className) {
            case \Greenter\Model\Retention\Retention::class:
                $name = 'retention';
                break;
            case \Greenter\Model\Perception\Perception::class:
                $name = 'perception';
                break;
            case \Greenter\Model\Despatch\Despatch::class:
                $name = 'despatch';
                break;
            case \Greenter\Model\Summary\Summary::class:
                $name = 'summary';
                break;
            case \Greenter\Model\Voided\Voided::class:
            case \Greenter\Model\Voided\Reversion::class:
                $name = 'voided';
                break;
            default:
                return '';
        }

        return $name.'.html.twig';
    }

    private function getHash(DocumentInterface $document)
    {
        $see = $this->getSee('');
        $xml = $see->getXmlSigned($document);

        $hash = (new \Greenter\Report\XmlUtils())->getHashSign($xml);

        return $hash;
    }

    private static function getParametersPdf($forma_pago, $guia)
    {
      $fullUrl = $_SERVER['DOCUMENT_ROOT'];
        $logo = file_get_contents($fullUrl.'/greenter/resources/logo.png');

        return [
            'system' => [
                'logo' => $logo,
                'hash' => ''
            ],
            'user' => [
                'resolucion' => '-',
                'header' => 'CEL: <b>957243080  - Of. 056-625071</b>',
                'extras' => [
                    ['name' => 'FORMA DE PAGO', 'value' => $forma_pago],
                    ['name' => 'N° GUIA', 'value' => $guia],
                    ['name' => 'VENDEDOR', 'value' => 'MULTISERVICIOS SOFER'],
                ],
            ]
        ];
    }

    private static function getParametersPdfCredito($forma_pago, $guia, $dias, $fecha_pago, $nroguia)
    {
      $fullUrl = $_SERVER['DOCUMENT_ROOT'];
        $logo = file_get_contents($fullUrl.'/greenter/resources/logo.png');
        //$fecha = new DateTime('+' . $dias . 'days');
        return [
            'system' => [
                'logo' => $logo,
                'hash' => ''
            ],
            'user' => [
                'resolucion' => '-',
                'header' => 'CEL: <b>957243080  - Of. 056-625071</b>',
                'extras' => [
                    ['name' => 'FORMA DE PAGO', 'value' => $forma_pago],
                    ['name' => 'DIAS DE CREDITO', 'value' => $dias],
                    ['name' => 'FECHA DE PAGO', 'value' => $guia],
                    ['name' => 'N° GUIA', 'value' => $nroguia],
                    ['name' => 'VENDEDOR', 'value' => 'MULTISERVICIOS SOFER'],
                ],
            ]
        ];
    }

    private static function getParametersPdfCreditoDetraccion($forma_pago, $guia, $dias, $fecha_pago, $nroguia)
    {
      $empresa = Empresas::find(1);
      $fullUrl = $_SERVER['DOCUMENT_ROOT'];
        $logo = file_get_contents($fullUrl.'/greenter/resources/logo.png');
        //$fecha = new DateTime('+' . $dias . 'days');
        return [
            'system' => [
                'logo' => $logo,
                'hash' => ''
            ],
            'user' => [
                'resolucion' => '-',
                'header' => 'CEL: <b>957243080  - Of. 056-625071</b>',
                'extras' => [
                    ['name' => 'CUENTA DE DETRACCIÓN', 'value' => $empresa->cta_detraccion],
                    ['name' => 'PORCENTAJE DE DETRACCIÓN', 'value' => $empresa->porcentaje_detraccion],
                    ['name' => 'FORMA DE PAGO', 'value' => $forma_pago],
                    ['name' => 'DIAS DE CREDITO', 'value' => $dias],
                    ['name' => 'FECHA DE PAGO', 'value' => $guia],
                    ['name' => 'N° GUIA', 'value' => $nroguia],
                    ['name' => 'VENDEDOR', 'value' => 'MULTISERVICIOS SOFER'],
                ],
            ]
        ];
    }
    private static function getParametersPdfDetraccion($forma_pago, $guia)
    {
      $empresa = Empresas::find(1);
      $fullUrl = $_SERVER['DOCUMENT_ROOT'];
        $logo = file_get_contents($fullUrl.'/greenter/resources/logo.png');

        return [
            'system' => [
                'logo' => $logo,
                'hash' => ''
            ],
            'user' => [
                'resolucion' => '-',
                'header' => 'CEL: <b>957243080  - Of. 056-625071</b>',
                'extras' => [
                  ['name' => 'CUENTA DE DETRACCIÓN', 'value' => $empresa->cta_detraccion],
                  ['name' => 'PORCENTAJE DE DETRACCIÓN', 'value' => $empresa->porcentaje_detraccion],
                  ['name' => 'FORMA DE PAGO', 'value' => $forma_pago],
                  ['name' => 'NRO GUIA', 'value' => $guia],
                  ['name' => 'VENDEDOR', 'value' => 'MULTISERVICIOS SOFER']
                ],
            ]
        ];
    }
}
