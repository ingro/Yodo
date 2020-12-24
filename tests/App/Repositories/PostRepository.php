<?php namespace App\Repositories;

use App\Models\PostWithEvents;
use App\Resolvers\Post\WithAuthorParamResolver;
use Illuminate\Database\Eloquent\Builder;
use Ingruz\Yodo\Base\Repository;

class PostRepository extends Repository
{
    protected $canSkipPagination = true;

    static $eagerAssociations = ['comments'];

    static $filterParams = ['title', 'comments.username'];

    public function getQueryParamsHandlers($requestParams)
    {
        return [
            'author' => 'author',
            'author_like' => function( Builder $query, $params) {
                return $query->where('author', 'LIKE', '%' . $params['author_like'] . '%');
            },
            'with_author' => WithAuthorParamResolver::class
        ];
    }

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
