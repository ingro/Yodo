<?php namespace Ingruz\Yodo\Base;

use Illuminate\Database\Eloquent\Builder;
use Ingruz\Yodo\Interfaces\QueryParamResolverInterface;

abstract class AbstractQueryParamsResolver implements QueryParamResolverInterface
{
    /**
     * @var Builder
     */
    protected $query;

    /**
     * @var array
     */
    protected $params;

    /**
     * AbstractQueryParamsResolver constructor.
     * @param Builder $query
     * @param array $params
     */
    public function __construct($query, $params = [])
    {
        $this->query = $query;
        $this->params = $params;
    }
}
