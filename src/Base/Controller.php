<?php namespace Ingruz\Yodo\Base;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use League\Fractal\Manager;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Item;
use League\Fractal\Resource\Collection;

use League\Fractal\Serializer\ArraySerializer;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

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
     * @var Model
     */
    protected $model;

    /**
     * ApiController constructor.
     * @param Repository $repository
     * @param Model $model
     */
    public function __construct(Repository $repository, Model $model)
    {
        $this->fractal = new Manager();
        $this->model = $model;

        $this->repository = new $repository(new $model);
    }

    /**
     * @param $request
     * @param $except
     * @return mixed
     */
    protected function getQueryParams($request, $except)
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
     */
    public function index(Request $request)
    {
        $data = $this->buildIndexData($request);

        return response()->json($data);
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
     * @param  \App\Api\Model $item
     * @return \Illuminate\Http\Response
     */
    public function show($item)
    {
        $data = $this->buildShowData($item);

        return response()->json($data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\Api\Model $item
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
     * @param  \App\Api\Model $item
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
     * @param \App\Api\Model $item
     * @return array
     */
    protected function serializeItem(Model $item)
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

        // Ritorno all data and manually add pagination's data
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
    protected function respondNotAuthorized()
    {
        return response()->json(['error' => 'You are not authorized!'], 403);
    }
}

