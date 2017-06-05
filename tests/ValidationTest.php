<?php namespace Ingruz\Yodo\Test;

use App\PostWithEvents;
use App\Repositories\PostRepository;
use Ingruz\Yodo\Exceptions\ModelValidationException;

class ValidationTest extends TestCase
{
    /**
     * @var PostRepository
     */
    protected $repository;

    public function setUp()
    {
        parent::setUp();

        $this->repository = new PostRepository();
    }

    public function testShouldWarnIfValidationFails()
    {
        $this->expectException(ModelValidationException::class);

        $this->expectExceptionMessage(json_encode(['title' => ['The title field is required.']]));

        $this->repository->create([
            'content' => 'foo bar'
        ]);
    }

    public function testEvents()
    {
        $repository = new PostRepository(new PostWithEvents());

        $res = $repository->create([
            'title' => 'My first post with events',
            'content' => 'foo bar'
        ]);

        $this->assertEquals('John Doe', $res->author);
    }
}
