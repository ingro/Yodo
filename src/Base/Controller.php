<?php namespace Ingruz\Yodo\Base;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Serializer\ArraySerializer;
use Ingruz\Yodo\Defaults\TransformerDefault;
use Ingruz\Yodo\Exceptions\ApiLimitNotValidException;
use Ingruz\Yodo\Exceptions\ModelValidationException;
use Ingruz\Yodo\Traits\ClassNameInspectorTrait;
use Spatie\Fractalistic\Fractal;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests, ClassNameInspectorTrait;

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
        $name = $this->getRelatedClassName('Controller', 'Repository', $ns);

        if (config('yodo.repositoriesNamespace')) {
            return config('yodo.repositoriesNamespace') . '\\' . $name;
        }

        $domain = $this->getRootNamespace($ns);

        return $domain . '\\Repositories\\' . $name;
    }

    /**
     * @return string
     */
    protected function getTransformerClass()
    {
        $ns = $this->getClassNameParts();
        $name = $this->getRelatedClassName('Controller', 'Transformer', $ns);

        if (config('yodo.transformersNamespace')) {
            $result = config('yodo.transformersNamespace') . '\\' . $name;
        } else {
            $domain = $this->getRootNamespace($ns);

            $result = $domain . '\\Transformers\\' . $name;
        }

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

        return $domain . '\\Models\\' . $name;
    }

    /**
     * @param Request $request
     * @param array $except
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
     * @param array $item
     * @return array
     */
    protected function buildShowData($item)
    {
        $item->load($this->repository->getEagerAssociations());

        return $this->serializeItem($item);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function index(Request $request)
    {
        try {
            $data = $this->buildIndexData($request);

            return response()->json($data);
        } catch (ApiLimitNotValidException $e) {
            return response()->json(['error' => $e->getMessage()], config('yodo.apiLimitExceptionHttpCode', 400));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function store(Request $request)
    {
        try {
            $result = $this->repository->create($request->all());

            return response()->json($this->serializeItem($result));
        } catch (ModelValidationException $e) {
            return response()->json(['error' => json_decode($e->getMessage())], config('yodo.modelValidationExceptionHttpCode', 422));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @param Model $item
     * @return \Illuminate\Http\JsonResponse
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
     * @param Request $request
     * @param Model $item
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function update(Request $request, $item)
    {
        try {
            $result = $this->repository->update($item, $request->all());

            return response()->json($this->serializeItem($result));
        } catch (ModelValidationException $e) {
            return response()->json(['error' => json_decode($e->getMessage())], config('yodo.modelValidationExceptionHttpCode', 422));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @param Model $item
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($item)
    {
        $result = $this->repository->delete($item);

        if ($result)
        {
            return response()->json(['result' => 'ok']);
        }
        // TODO: else return error?
    }

    /**
     * @param Model $item
     * @return array
     */
    protected function serializeItem($item)
    {
        return Fractal::create()
            ->item($item)
            ->transformWith(new $this->transformerClass)
            ->serializeWith(ArraySerializer::class)
            ->toArray();
    }

    /**
     * @param LengthAwarePaginator|array $items
     * @param array $queryParams
     * @return array
     */
    protected function serializeCollection($items, $queryParams = [])
    {
        if (get_class($items) === LengthAwarePaginator::class)
        {
            $items->appends($queryParams);

            return Fractal::create()
                ->collection($items->getCollection())
                ->transformWith(new $this->transformerClass)
                ->paginateWith(new IlluminatePaginatorAdapter($items))
                ->toArray();
        }

        $data = Fractal::create()
            ->collection($items)
            ->transformWith(new $this->transformerClass)
            ->toArray();

        return [
            'data' => $data['data'],
            'meta' => [
                'pagination' => [
                    'total' => count($data['data']),
                    'count' => count($data['data']),
                    'per_page' => 0,
                    'current_page' => 1,
                    'total_pages' => 1,
                    'links' => []
                ]
            ]
        ];
    }
}

