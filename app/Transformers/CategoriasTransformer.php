<?php 

namespace App\Transformers;

use League\Fractal\TransformerAbstract;

use App\Models\Categorias;

class CategoriasTransformer extends TransformerAbstract
{
    public function transform(Categorias $categorias)
    {
        return [
            'id' => $categorias->id,
            'nombre' => $categorias->nombre,
            'descripcion' => $categorias->descripcion,
            'estado' => $categorias->estado,
            'created_for' => $categorias->created_for,
            'updated_for' => $categorias->updated_for
        ];

        
    }
}