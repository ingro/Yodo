<?php namespace App\Repositories;

use App\PostWithEvents;
use Ingruz\Yodo\Base\Repository;

class PostRepository extends Repository
{
    static $eagerAssociations = ['comments'];

    static $filterParams = ['title', 'comments.username'];

    static $rules = [
        'save' => [
            'title' => 'required'
        ],
        'create' => [],
        'update' => []
    ];

    public function boot()
    {
        PostWithEvents::saving(function($model) {
            $model->author = 'John Doe';
        });
    }
}
