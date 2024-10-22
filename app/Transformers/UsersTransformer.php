<?php 

namespace App\Transformers;

use League\Fractal\TransformerAbstract;

use App\Models\User;

class UsersTransformer extends TransformerAbstract
{
    public function transform(User $user)
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'documento' => $user->documento,
            'tipo_usuario' => $user->tipo_usuario
        ];
    }
}