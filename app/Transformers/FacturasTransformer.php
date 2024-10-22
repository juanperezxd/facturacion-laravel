<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;

use App\Models\Facturas;

class FacturasTransformer extends TransformerAbstract
{
    public function transform(Facturas $facturas)
    {

        return [
          'id' => $facturas->id,
          'razon_social' => $facturas->cliente_setRznSocial,
          'precio' => $facturas->setMtoImpVenta,
          'boleta' => $facturas->setSerie.'-'.$facturas->setCorrelativo,
          'fecha'  => $facturas->setFechaEmision,
          'estado' => $facturas->estado.' - '.$facturas->estadoDesc,
          'document_name' => $facturas->document_name,
          'ticket' => $facturas->ticket,
          'descripcion' => 'CREACION DE RESUMEN DIARIO: '.$facturas->document_name,
        ];


    }
}
