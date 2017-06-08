<?php namespace Ingruz\Yodo\Test;

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
}
