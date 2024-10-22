<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;

use App\Models\Movimientos;

class SalidasTransformer extends TransformerAbstract
{
    public function transform(Movimientos $movimiento)
    {

        return [
            'id' => $movimiento->id,
            'tipo_doc' => $movimiento->tipo_doc,
            'num_doc' => $movimiento->num_doc,
            'tipo_mov' => $movimiento->tipo_movimiento,
            'proveedor' => $movimiento->clientes->razon_social,
            'fecha' => $movimiento->fecha,
            'facturado' => $movimiento->facturado,
            'document_name' => $movimiento->document_name
        ];


    }
}
