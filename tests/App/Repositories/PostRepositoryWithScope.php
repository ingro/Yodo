<?php namespace App\Repositories;

use App\Models\PostWithEvents;
use App\Resolvers\Post\WithAuthorParamResolver;
use Illuminate\Database\Eloquent\Builder;
use Ingruz\Yodo\Base\Repository;

class PostRepositoryWithScope extends Repository
{
    static $defaultScopes = ['withTopRating'];
}
