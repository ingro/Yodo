<?php

use App\Post;
use App\Repositories\PostRepository;
use Ingruz\Yodo\Test\TestCase;

class RepositoryTest extends TestCase
{
    public function testIsInstantiable()
    {
        $item = new Post();

        $repository = new PostRepository($item);

        $res = $repository->getAll();

        $this->assertCount(50, $res);

        $response = $this->json('GET', '/api/posts?limit=5');

        dump($response->getContent());
    }
}
