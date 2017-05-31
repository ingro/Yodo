<?php

use App\Post;
use App\Repositories\PostRepository;
use Ingruz\Yodo\Test\TestCase;

class RepositoryTest extends TestCase
{
    /**
     * @var PostRepository
     */
    protected $repository;

    public function setUp()
    {
        parent::setUp();

        $this->repository = new PostRepository(new Post);
    }

    public function testSimpleGetAll()
    {
        $res = $this->repository->getAll();

        $this->assertCount(50, $res);

        $this->assertInstanceOf(Post::class, $res[0]);
    }

    public function testGetById()
    {
        $res = $this->repository->getById(5);

        $this->assertInstanceOf(Post::class, $res);

        $this->assertEquals(5, $res->id);
    }

    public function testCreate()
    {
        $res = $this->repository->create([
            'title' => 'A test post',
            'content' => 'Lorem ipsum'
        ]);

        $this->assertInstanceOf(Post::class, $res);
        $this->assertEquals(101, $res->id);
        $this->assertEquals('A test post', $res->title);
        $this->assertEquals('Lorem ipsum', $res->content);
    }

    public function testUpdateByIstance()
    {
        $item = Post::find(5);

        $res = $this->repository->update($item, [
            'title' => 'Updated title'
        ]);

        $updated = Post::find(5);

        $this->assertInstanceOf(Post::class, $res);
        $this->assertEquals('Updated title', $updated->title);
    }

    public function testUpdateById()
    {
        $res = $this->repository->update(42, [
            'title' => 'Another title'
        ]);

        $updated = Post::find(42);

        $this->assertInstanceOf(Post::class, $res);
        $this->assertEquals('Another title', $updated->title);
    }

    public function testDelete()
    {
        $res = $this->repository->delete(57);

        $this->assertTrue($res);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Post::findOrFail(57);
    }
}
