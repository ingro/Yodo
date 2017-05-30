<?php namespace Ingruz\Yodo\Test;

use Ingruz\Yodo\Base\Repository;

class TestRepository extends Repository
{
    function __construct(TestModel $model)
    {
        $this->model = $model;
    }
}
