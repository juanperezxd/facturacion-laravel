<?php 

namespace App\Transformers;

use League\Fractal\TransformerAbstract;

use App\Models\Series;

class SeriesTransformer extends TransformerAbstract
{
    public function transform(Series $series)
    {
        return [
            'id' => $series->id,
            'descripcion' => $series->descripcion,
            'tipo_documento' => $series->tipo_documento,
            'serie' => $series->serie,
            'correlativo' => $series->correlativo,
            'estado' => $series->estado,
        ];
    }
}