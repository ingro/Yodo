<?php namespace App\Repositories;

use App\Post;
use Ingruz\Yodo\Base\Repository;

class PostRepository extends Repository
{
    static $eagerAssociations = ['comments'];

    function __construct(Post $model)
    {
        $this->model = $model;
    }
}
