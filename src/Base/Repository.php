<?php namespace Ingruz\Yodo\Base;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Ingruz\Yodo\Exceptions\ApiLimitNotValidException;
use Ingruz\Yodo\Exceptions\InvalidQueryParamResolver;
use Ingruz\Yodo\Exceptions\ModelValidationException;
use Ingruz\Yodo\Traits\ClassNameInspectorTrait;
use Ingruz\Yodo\Helpers\RulesMerger;

class Repository
{
    use ClassNameInspectorTrait;

    /**
     * @var Model
     */
    protected $model;

    /**
     * @var bool
     */
    protected $canSkipPagination = false;

    /**
     * @var int
     */
    protected $limitCap = 100;

    /**
     * @var array
     */
    static $eagerAssociations = [];

    /**
     * @var array
     */
    static $defaultParams = [
        'limit' => 50
    ];

    /**
     * @var array
     */
    static $filterParams = [];

    /**
     * @var array
     */
    static $queryParamsHandlers = [];

    /**
     * @var array
     */
    static $orderParamsHandlers = [];

    /**
     * @var array
     */
    static $defaultScopes = [];

    /**
     * @var array
     */
    static $rules = [
        'save' => [],
        'create' => [],
        'update' => []
    ];

    /**
     * Repository constructor.
     * @param null $model
     */
    public function __construct($model = null)
    {
        // If a model is passed as argument use that, otherwise try to build one from repository's classname
        if ($model and $model instanceof Model) {
            $this->model = $model;
        } else if ($model and is_string($model)) {
            $this->model = app($model);
        } else {
            $this->model = app($this->getModelClass());
        }

        $this->boot();
    }

    /**
     *
     */
    public function boot()
    {
        //
    }

    /**
     * @return string
     */
    protected function getModelClass()
    {
        $ns = $this->getClassNameParts();
        $domain = $this->getRootNamespace($ns);
        $name = $this->getRelatedClassName('Repository', '', $ns);

        return $domain . '\\Models\\' . $name;
    }

    /**
     * @return Model
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @param array $params
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|Builder[]|\Illuminate\Database\Eloquent\Collection
     * @throws ApiLimitNotValidException
     * @throws InvalidQueryParamResolver
     */
    public function getAll($params = [])
    {
        $params = $this->getParams($params);

        $query = $this->getQuery($params);

        if ($params['limit'] == 0 and ! $this->canSkipPagination) {
            throw new ApiLimitNotValidException('Please set a limit greather than 0');
        }

        if ($params['limit'] > $this->limitCap) {
            throw new ApiLimitNotValidException('Please set a limit below ' . $this->limitCap);
        }

        if ($params['limit'] == 0 and $this->canSkipPagination) {
            return $query->get();
        }

        // "page" parameter is automatically retrieved
        $result = $query->paginate($params['limit']);

        return $result;
    }

    /**
     * @param array $requestParams
     * @return array
     */
    public function getQueryParamsHandlers($requestParams) {
        return static::$queryParamsHandlers;
    }

    /**
     * @return array
     */
    public function getOrderParamsHandlers() {
        return static::$orderParamsHandlers;
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function getById($id)
    {
        return $this->model->findOrFail($id);
    }

    /**
     * @param  string $key
     * @param  mixed $value
     * @param  string $operand
     * @return mixed
     */
    public function getFirstBy($key, $value, $operand = '=')
    {
        return $this->model->where($key, $operand, $value)->first();
    }

     /**
     * @param  string $key
     * @param  mixed $value
     * @param string $operand
     * @return mixed
     */
    public function getManyBy($key, $value, $operand = '=')
    {
        return $this->model->where($key, $operand, $value)->get();
    }

    /**
     * @param array $data
     * @return Model
     * @throws ModelValidationException
     */
    public function create(array $data)
    {
        $this->validate('create', $data);

        return $this->model->create($data);
    }

    /**
     * @param mixed $item
     * @param array $data
     * @return bool|Model
     * @throws ModelValidationException
     */
    public function update($item, $data)
    {
        $this->validate('update', $data);

        if ($item instanceof Model)
        {
            $instance = $item;
        } else
        {
            $instance = $this->getById($item);

            if (! $instance)
            {
                return false;
            }
        }

        $instance->update($data);

        return $instance;
    }

    /**
     * @param $item
     * @return bool|null
     * @throws \Exception
     */
    public function delete($item)
    {
        if ($item instanceof Model)
        {
            $istance = $item;
        } else
        {
            $istance = $this->getById($item);

            if (! $istance)
            {
                return false;
            }
        }

        return $istance->delete();
    }

    /**
     * @param array $params
     * @return Builder|Model
     * @throws InvalidQueryParamResolver
     */
    protected function getQuery($params)
    {
        // Include all the releations to eager load declared in the repository
        $query = $this->model->with($this->getEagerAssociations());

        foreach ($this->getDefaultScopes() as $scope) {
            $query->$scope();
        }

        // Obtain filter's params declared in the repository
        $queryParams = $this->getQueryParamsHandlers($params);

        // Cycle them...
        foreach ($queryParams as $queryParam => $dbAttr)
        {
            // Check if the key is present in the query string hash
            if (isset($params[$queryParam]))
            {
                // If the value is a function call it
                if (is_callable($dbAttr)) {
                    $query = $dbAttr($query, $params);
                } else if (class_exists($dbAttr)) {
                    $resolver = new $dbAttr($query, $params);

                    if ($resolver instanceof AbstractQueryParamsResolver) {
                        $query = $resolver->resolve();
                    } else {
                        throw new InvalidQueryParamResolver();
                    }
                } else {
                    $value = $params[$queryParam];

                    // If the string starts with :: it represent a scope
                    if (strpos($dbAttr, '::') !== false) {
                        $scope = str_replace('::', '', $dbAttr);

                        $query->$scope($value);
                    // Otherwise, if the value is a composed key (containing a .) set a whereHas on the relation
                    } else if (strpos($dbAttr, '.') !== false) {
                        $parts = explode('.', $dbAttr);

                        $query->whereHas($parts[0], function(Builder $q) use($parts, $value) {
                            $q->where($parts[1], $value);
                        });
                    } else {
                        // Else, just add a simple where to the query
                        $query->where($dbAttr, $value);
                    }
                }
            }
        }

        // If the key `filter` is present into the query string
        if (isset($params['filter']))
        {
            // Check if there are columns where to execute a full text search
            if (count(static::$filterParams) > 0)
            {
                $term = $params['filter'];
                $filterParams = static::$filterParams;

                // Add a where to the query for every column set
                $query->where(function(Builder $q) use ($filterParams, $term)
                {
                    foreach ($filterParams as $field)
                    {
                        // if the field is a composed key (containing a .) use orWhereHas on the relation
                        if (strpos($field, '.') !== false)
                        {
                            $parts = explode('.', $field);

                            $q->orWhereHas($parts[0], function(Builder $rq) use($parts, $term) {
                                $rq->where($parts[1], 'LIKE', '%' . $term . '%');
                            });
                        } else {
                            $q->orWhere($field, 'LIKE', '%' . $term . '%');
                        }
                    }
                });
            }
        }

        // If the key `orderBy` is present into the query string
        if (isset($params['orderBy']))
        {
            // First obtain direction
            $orderDir = isset($params['orderDir']) ? $params['orderDir'] : 'asc';

            // Obtain the order handlers from the repository
            $orderParams = $this->getOrderParamsHandlers();

            // Check if the key is set into the handlers
            if (isset($orderParams[$params['orderBy']]))
            {
                // If the value is a function call it
                if (is_callable($orderParams[$params['orderBy']]))
                {
                    $action = $orderParams[$params['orderBy']];
                    $query = $action($query, $orderDir);
                } else
                {
                    // Otherwise use it as column to order by
                    $query = $query->orderBy($orderParams[$params['orderBy']], $orderDir);
                }
            // Else just use the name as column to order by
            } else
            {
                $query = $query->orderBy($params['orderBy'], $orderDir);
            }
        }

        return $query;
    }

    /**
     * @param array $params
     * @return array
     */
    protected function getParams(array $params = [])
    {
        $data = static::$defaultParams;

        foreach ($params as $param => $value)
        {
            $data[$param] = $value;
        }

        return $data;
    }

    /**
     * @return array
     */
    public function getEagerAssociations()
    {
        return static::$eagerAssociations;
    }

    /**
     * @return array
     */
    public function getDefaultScopes()
    {
        return static::$defaultScopes;
    }

    /**
     * @param $op
     * @param $data
     * @return bool
     * @throws ModelValidationException
     */
    protected function validate($op, $data)
    {
        $rules = $this->getValidationRules($op);

        if (empty($rules))
        {
            return true;
        }

        $factory = app('validator');

        $validator = $factory->make($data, $rules);

        if (! $validator->passes())
        {
            throw new ModelValidationException($validator->messages());
        }

        return true;
    }

    /**
     * Return a single array with the rules for the action required
     *
     * @param string $op
     * @return array
     */
    protected function getValidationRules($op)
    {
        return RulesMerger::merge(static::$rules, $op);
    }
}
