<?php 

namespace App\Transformers;

use League\Fractal\TransformerAbstract;

use App\Models\Gastos;

class GastosTransformer extends TransformerAbstract
{
    public function transform(Gastos $gastos)
    {

        return [
            'id' => $gastos->id,
            'fecha' => $gastos->fecha,
            'descripcion' => $gastos->descripcion,
            'monto' => $gastos->monto,
            'usuario' => $gastos->users->name
        ];

        
    }
}