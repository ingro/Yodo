<?php namespace Ingruz\Yodo\Test;

class ControllerTest extends TestCase
{
    public function testIndexRoute()
    {
        $response = $this->json('GET', '/api/posts?limit=5');

        // dump($response->getContent());
    }
}
