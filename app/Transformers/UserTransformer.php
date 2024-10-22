<?php 

namespace App\Transformers;

use League\Fractal\TransformerAbstract;

use App\Models\User;

class UserTransformer extends TransformerAbstract
{
    public function transform(User $user)
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'ape_paterno' => $user->ape_paterno,
            'ape_materno' => $user->ape_materno,
            'email' => $user->email,
        ];
    }
}