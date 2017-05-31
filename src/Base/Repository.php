<?php namespace Ingruz\Yodo\Base;

use Illuminate\Database\Eloquent\Model;
use Ingruz\Yodo\Exceptions\ApiLimitNotSetException;

class Repository
{
    /**
     * @var Model
     */
    protected $model;

    /**
     * @var bool
     */
    protected $canSkipPagination = false;

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
    static $queryParams = [];

    /**
     * @var array
     */
    static $orderParams = [];

    /**
     * Repository constructor.
     * @param Model|null $model
     */
    public function __construct(Model $model = null)
    {
        // If a model is passed as argument use that, otherwise try to build one from repository's classname
        if ($model) {
            $this->model = $model;
        } else {
            $this->model = app($this->getModelClass());
        }
    }

    /**
     * @return string
     */
    protected function getModelClass()
    {
        $ns = explode('\\', get_called_class());
        $domain = reset($ns);
        $name = str_replace('Repository', '', end($ns));

        return $domain . '\\' . $name;
    }

    /**
     * @param array $params
     * @return mixed
     * @throws ApiLimitNotSetException
     */
    public function getAll($params = [])
    {
        $params = $this->getParams($params);

        $query = $this->getQuery($params);

        if ($params['limit'] == 0 and ! $this->canSkipPagination) {
            throw new ApiLimitNotSetException('A limit greater than 0 must be set for this api request!');
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
    public function getQueryParams($requestParams) {
        return static::$queryParams;
    }

    /**
     * @return array
     */
    public function getOrderParams() {
        return static::$orderParams;
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
     * Create a new item
     *
     * @param  array $data
     * @return Model
     */
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * @param mixed $item
     * @param array $data
     * @return bool
     */
    public function update($item, $data)
    {
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
     * Delete an item
     *
     * @param  mixed $item
     * @return boolean
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
     * @param $params
     * @return mixed
     */
    protected function getQuery($params)
    {
        // Includo tutte le relazioni dichiarate per il repository
        $query = $this->model->with($this->getEagerAssociations());

        // Ottengo i parametri dei filtri impostati per il repository
        $queryParams = $this->getQueryParams($params);

        // Li ciclo...
        foreach ($queryParams as $queryParam => $dbAttr)
        {
            // Se la chiave è presente nella query string
            if (isset($params[$queryParam]))
            {
                // Se il valore è una funzione allora aggiorno la query eseguendola
                if (is_callable($dbAttr)) {
                    $query = $dbAttr($query, $params);
                } else {
                    // Altrimenti, se il valore è una chiave composta (comprende un .) imposto un where sulla relazione
                    $value = $params[$queryParam];

                    if (strpos($dbAttr, '.') !== false)
                    {
                        $parts = explode('.', $dbAttr);

                        $query = $query->whereHas($parts[0], function($q) use($parts, $value) {
                            $q->where($parts[1], $value);
                        });
                    } else {
                        // Altrimenti imposto un where semplice su un attributo
                        $query = $query->where($dbAttr, $value);
                    }
                }
            }
        }

        // Se è presente la chiave filter nella query string
        if (isset($params['filter']))
        {
            // Controllo che ci siano parametri su cui filtrare
            if (count(static::$filterParams) > 0)
            {
                $term = $params['filter'];
                $filterParams = static::$filterParams;

                // Aggiungo un where alla query in modo che matchi per tutti i parametri desiderati
                $query->where(function($q) use ($filterParams, $term)
                {
                    foreach ($filterParams as $field)
                    {
                        $q->orWhere($field, 'LIKE', '%' . $term . '%');
                    }
                });
            }
        }

        // Se è presente la chiave orderBy nella query string
        if (isset($params['orderBy']))
        {
            // Determino prima la direzione
            $orderDir = isset($params['orderDir']) ? $params['orderDir'] : 'asc';

            $orderParams = $this->getOrderParams();

            // Se è impostata la chiave nell'array $orderParams...
            if (isset($orderParams[$params['orderBy']]))
            {
                // Se il valore è una funzione la eseguo e la aggiungo alla query
                if (is_callable($orderParams[$params['orderBy']]))
                {
                    $action = $orderParams[$params['orderBy']];
                    $query = $action($query, $orderDir);
                } else
                {
                    // Altrimenti uso il valore come chiave per l'ordinamento
                    $query = $query->orderBy($orderParams[$params['orderBy']], $orderDir);
                }
            // Altrimenti aggiungo alla query un ordinamento standard
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
}
