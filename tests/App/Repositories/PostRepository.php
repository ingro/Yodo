<?php namespace App\Repositories;

use App\Post;
use Ingruz\Yodo\Base\Repository;

class PostRepository extends Repository
{
    function __construct(Post $model)
    {
        $this->model = $model;
    }
}
