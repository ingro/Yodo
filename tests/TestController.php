<?php namespace Ingruz\Yodo\Test;

use Ingruz\Yodo\Base\Controller;

class TestController extends Controller
{
    protected $transformerClass = TestTransformer::class;

    /**
     * OrdersController constructor.
     * @param TestRepository $repository
     * @param TestModel $model
     */
    public function __construct(TestRepository $repository, TestModel $model)
    {
        parent::__construct($repository, $model);
    }
}
