<?php

use Ingruz\Yodo\Test\TestModel;

class ModelTest extends Orchestra\Testbench\TestCase
{
    public function testIsInstantiable()
    {
        $data = ['foo' => 'bar'];

        $item = new TestModel();

        $this->assertEquals($data['foo'], 'bar');
    }
}