<?php namespace Ingruz\Yodo\Base;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use Ingruz\Yodo\Defaults\TransformerDefault;
use Ingruz\Yodo\Exceptions\ApiLimitNotValidException;
use Ingruz\Yodo\Traits\ClassNameInspectorTrait;
use League\Fractal\Manager;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Item;
use League\Fractal\Resource\Collection;

use League\Fractal\Serializer\ArraySerializer;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests, ClassNameInspectorTrait;

    /**
     * @var Manager
     */
    protected $fractal;

    /**
     * @var Repository
     */
    protected $repository;

    /**
     * @var string
     */
    protected $transformerClass;

    /**
     * Controller constructor.
     */
    public function __construct()
    {
        $this->fractal = new Manager();

        $this->repository = $this->getRepository();
        $this->transformerClass = $this->getTransformerClass();
    }

    /**
     * @return Repository
     */
    protected function getRepository()
    {
        // Try to get repository's classname
        $className = $this->getRepositoryClass();

        if (class_exists($className)) {
            return app($className);
        }

        // It that doesn't exist use a default one by passing the right model's classname to the base repository
        $modelClass = $this->getModelClass();

        return new Repository(app($modelClass));
    }

    /**
     * @return string
     */
    protected function getRepositoryClass()
    {
        $ns = $this->getClassNameParts();
        $domain = $this->getRootNamespace($ns);
        $name = $this->getRelatedClassName('Controller', 'Repository', $ns);

        return $domain . '\\Repositories\\' . $name;
    }

    /**
     * @return string
     */
    protected function getTransformerClass()
    {
        $ns = $this->getClassNameParts();
        $domain = $this->getRootNamespace($ns);
        $name = $this->getRelatedClassName('Controller', 'Transformer', $ns);

        $result = $domain . '\\Transformers\\' . $name;

        if (class_exists($result)) {
            return $result;
        }

        // If the class doen't exists return a default transformer
        return TransformerDefault::class;
    }

    /**
     * @return string
     */
    protected function getModelClass()
    {
        $ns = $this->getClassNameParts();
        $domain = $this->getRootNamespace($ns);
        $name = $this->getRelatedClassName('Controller', '', $ns);

        return $domain . '\\' . $name;
    }

    /**
     * @param $request
     * @param $except
     * @return mixed
     */
    protected function getQueryParams($request, $except = [])
    {
        return $request->except($except);
    }

    /**
     * @param Request $request
     * @param array $except
     * @return array
     */
    protected function buildIndexData(Request $request, $except = [])
    {
        $queryParams = $this->getQueryParams($request, $except);

        $items = $this->repository->getAll($queryParams);

        return $this->serializeCollection($items, $queryParams);
    }

    /**
     * @param $item
     * @return array
     */
    protected function buildShowData($item)
    {
        $item->load($this->repository->getEagerAssociations());

        return $this->serializeItem($item);
    }

    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function index(Request $request)
    {
        try {
            $data = $this->buildIndexData($request);

            return response()->json($data);
        } catch (ApiLimitNotValidException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $result = $this->repository->create($request->all());

        if ($result)
        {
            return response()->json($this->serializeItem($result));
        }

        // TODO: return error
    }

    /**
     * Display the specified resource.
     *
     * @param  \Illuminate\Database\Eloquent\Model|string $item
     * @return \Illuminate\Http\Response
     */
    public function show($item)
    {
        if (is_string($item)) {
            $item = $this->repository->getById($item);
        }

        $data = $this->buildShowData($item);

        return response()->json($data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Illuminate\Database\Eloquent\Model $item
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $item)
    {
        $result = $this->repository->update($item, $request->all());

        if ($result)
        {
            return response()->json($this->serializeItem($result));
        }

        // TODO: return error
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Database\Eloquent\Model $item
     * @return \Illuminate\Http\Response
     */
    public function destroy($item)
    {
        $result = $this->repository->delete($item);

        if ($result)
        {
            return response()->json(['result' => 'ok']);
        }
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $item
     * @return array
     */
    protected function serializeItem($item)
    {
        $this->fractal->setSerializer(new ArraySerializer());
        $resource = new Item($item, new $this->transformerClass);
        return $this->fractal->createData($resource)->toArray();
    }

    /**
     * @param $items
     * @param array $queryParams
     * @return array
     */
    protected function serializeCollection($items, $queryParams = [])
    {
        $resource = new Collection($items, new $this->transformerClass);

        // Return paginated data
        if (get_class($items) === LengthAwarePaginator::class)
        {
            $items->appends($queryParams);
            $resource->setPaginator(new IlluminatePaginatorAdapter($items));

            return $this->fractal->createData($resource)->toArray();
        }

        // Return all data and manually add pagination's data
        $results = $this->fractal->createData($resource)->toArray();

        return [
            'data' => $results['data'],
            'meta' => [
                'pagination' => [
                    'total' => count($results['data']),
                    'count' => count($results['data']),
                    'perPage' => 0,
                    'current_page' => 1,
                    'total_pages' => 1,
                    'links' => []
                ]
            ]
        ];
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    /*protected function respondNotAuthorized()
    {
        return response()->json(['error' => 'You are not authorized!'], 403);
    }*/
}

