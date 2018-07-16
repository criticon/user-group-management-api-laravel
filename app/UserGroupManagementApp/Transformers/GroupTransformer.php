<?php

namespace App\UserGroupManagementApp\Transformers;

class GroupTransformer extends Transformer
{
    public function transform($group)
    {
        return [
            'id' => $group['id'],
            'name' => $group['name'],
            'users' => array_pluck($group['users'], 'id')
        ];
    }
}
