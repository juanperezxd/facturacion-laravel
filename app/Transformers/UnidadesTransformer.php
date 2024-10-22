<?php 

namespace App\Transformers;

use League\Fractal\TransformerAbstract;

use App\Models\Unidades;

class UnidadesTransformer extends TransformerAbstract
{
    public function transform(Unidades $unidades)
    {

        return [
            'id' => $unidades->id,
            'nombre' => $unidades->nombre,
            'simbolo' => $unidades->simbolo,
            'estado' => $unidades->estado,
            'created_for' => $unidades->created_for,
            'updated_for' => $unidades->updated_for
        ];

        
    }
}