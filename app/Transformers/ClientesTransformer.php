<?php 

namespace App\Transformers;

use League\Fractal\TransformerAbstract;

use App\Models\Clientes;

class ClientesTransformer extends TransformerAbstract
{
    public function transform(Clientes $clientes)
    {

        return [
            'id' => $clientes->id,
            'tipo_documento' => $clientes->tipo_documento,
            'documento' => $clientes->documento,
            'razon_social' => $clientes->razon_social,
            'direccion' => $clientes->direccion,
            'tipo_cliente' => $clientes->tipo_cliente,
            'created_for' => $clientes->created_for,
            'updated_for' => $clientes->updated_for,
        ];

        
    }
}