<?php namespace Ingruz\Yodo\Test;

use App\Post;
use App\Comment;
use App\PostWithEvents;
use App\Repositories\PostRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Ingruz\Yodo\Exceptions\ApiLimitNotValidException;

class RepositoryTest extends TestCase
{
    /**
     * @var PostRepository
     */
    protected $repository;

    public function setUp(): void
    {
        parent::setUp();

        $this->repository = new PostRepository();
    }

    public function testInstantiation() {
        $repo = new PostRepository();

        $this->assertInstanceOf(Post::class, $repo->getModel());

        $repo = new PostRepository(new Post());

        $this->assertInstanceOf(Post::class, $repo->getModel());

        $repo = new PostRepository(PostWithEvents::class);

        $this->assertInstanceOf(PostWithEvents::class, $repo->getModel());
    }

    public function testSimpleGetAll()
    {
        $res = $this->repository->getAll();

        $this->assertCount(50, $res);

        $this->assertInstanceOf(Post::class, $res[0]);
    }

    public function testGetAllFiltered()
    {
        for($i = 0; $i < 3; $i++) {
            Post::factory()->create([
                'author' => 'pincopanco'
            ]);
        }

        for($i = 0; $i < 7; $i++) {
            Post::factory()->create([
                'author' => 'pancopinco'
            ]);
        }

        $res = $this->repository->getAll(['author' => 'pincopanco']);

        $this->assertCount(3, $res);
        
        $res = $this->repository->getAll(['author_like' => 'panco']);

        $this->assertCount(10, $res);

        $res = $this->repository->getAll(['with_author' => 't']);

        $this->assertCount(10, $res);
    }

    public function testGetById()
    {
        $res = $this->repository->getById(5);

        $this->assertInstanceOf(Post::class, $res);

        $this->assertEquals(5, $res->id);
    }

    public function testGetByIdNotFound()
    {
        $this->expectException(ModelNotFoundException::class);

        $this->repository->getById(999);
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

        $this->expectException(ModelNotFoundException::class);

        Post::findOrFail(57);
    }

    public function testApiLimitValidation()
    {
        $this->expectException(ApiLimitNotValidException::class);
        $this->expectExceptionMessage('Please set a limit below 100');

        $this->repository->getAll([
            'limit' => 500
        ]);

        $this->expectException(ApiLimitNotValidException::class);
        $this->expectExceptionMessage('Please set a limit greather than 0');

        $this->repository->getAll([
            'limit' => 0
        ]);
    }

    public function testFilteringByRelation()
    {
        $post = $this->repository->create([
            'title' => 'A test post',
            'content' => 'Lorem ipsum'
        ]);

        Comment::factory()->create([
            'post_id' => $post->id,
            'username' => 'King Aegon Targaryen'
        ]);

        $res = $this->repository->getAll(['filter' => 'aegon']);

        $this->assertCount(1, $res);
    }
}
