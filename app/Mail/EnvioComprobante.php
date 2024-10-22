<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\Facturas;

class EnvioComprobante extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(Facturas $facturas)
    {
        $this->facturas = $facturas;
    }


    public function build()
    {
        $fileXml= public_path(). "greenter/files/".$this->facturas->document_name.".xml";
        $filePdf= public_path(). "greenter/files/".$this->facturas->document_name.".pdf";

        //return $this->view('view.name');
        return $this->from('juanjo.dhw@gmail.com', 'Equipo Sofer')
                ->subject('Comprobante de pago')
                ->view('mail.comprobante')
                ->attach($filePdf, [
                    'as' => 'Comprobante.pdf',
                    'mime' => 'application/pdf',
                ])
                ->attach($fileXml, [
                    'as' => 'Comprobante.xml',
                    'mime' => 'application/xml',
                ]);
    }
}
