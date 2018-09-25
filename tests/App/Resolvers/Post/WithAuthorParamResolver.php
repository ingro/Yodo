<?php namespace App\Resolvers\Post;

use Ingruz\Yodo\Base\AbstractQueryParamsResolver;

class WithAuthorParamResolver extends AbstractQueryParamsResolver
{
    public function resolve()
    {
        return $this->query->whereNotNull('author');
    }
}
