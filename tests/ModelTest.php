<?php

use Ingruz\Yodo\Test\TestCase;
use Ingruz\Yodo\Test\TestModel;
use Ingruz\Yodo\Test\TestRepository;

class ModelTest extends TestCase
{
    public function testIsInstantiable()
    {
        $item = new TestModel();

        $repository = new TestRepository($item);

        $res = $repository->getAll();

        $this->assertCount(50, $res);

        $response = $this->json('GET', '/api/posts?limit=5');

        dump($response->getContent());
    }
}
