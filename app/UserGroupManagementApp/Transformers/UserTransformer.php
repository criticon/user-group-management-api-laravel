<?php

namespace App\UserGroupManagementApp\Transformers;

class UserTransformer extends Transformer
{
    public function transform($user)
    {
        return [
            'id' => $user['id'],
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name'],
            'email' => $user['email'],
            'state' => $user['active'] ? 'active' : 'non active'
        ];
    }
}
