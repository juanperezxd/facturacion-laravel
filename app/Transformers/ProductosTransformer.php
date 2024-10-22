<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;

use App\Models\Productos;

class ProductosTransformer extends TransformerAbstract
{
    public function transform(Productos $productos)
    {

        return [
            'id' => $productos->id,
            'nombre' => $productos->nombre,
            'codigo' => $productos->codigo,
            'unidad' => $productos->unidades->nombre,
            'categoria' => $productos->categorias->nombre,
            'precio_venta' => $productos->precio_venta,
            'precio_compra' => $productos->precio_compra,
            'impuesto' => number_format($productos->impuestos->tasa,2).' - '.$productos->impuestos->nombre,
            'stock' => $productos->stock,
            'created_for' => $productos->created_for,
            'updated_for' => $productos->updated_for
        ];


    }
}
