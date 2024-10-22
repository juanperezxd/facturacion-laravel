<?php 

namespace App\Transformers;

use League\Fractal\TransformerAbstract;

use App\Models\Facturas;

class VentasTransformer extends TransformerAbstract
{
    public function transform(Facturas $facturas)
    {
        if($facturas->detraccion == 1){
            $detraccion = 'SI';
        }else{
            $detraccion = 'NO';
        }
        return [
            'id' => $facturas->id,
            'razon_social' => $facturas->cliente_setRznSocial,
            'total' => $facturas->setMtoImpVenta,
            'fecha' => $facturas->setFechaEmision,
            'forma_pago' => $facturas->forma_pago,
            'detraccion' => $detraccion,
            'documentos' => $facturas->setSerie.'-'.$facturas->setCorrelativo,
            'estado' => $facturas->estadoDesc,
        ];
    }
}