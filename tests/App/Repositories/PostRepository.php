<?php namespace App\Repositories;

use Ingruz\Yodo\Base\Repository;

class PostRepository extends Repository
{
    static $eagerAssociations = ['comments'];

    static $rules = [
        'save' => [
            'title' => 'required'
        ],
        'create' => [],
        'update' => []
    ];
}
