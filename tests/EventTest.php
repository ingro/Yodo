<?php namespace Ingruz\Yodo\Test;

use App\Models\PostWithEvents;
use App\Repositories\PostRepository;

class EventTest extends TestCase
{
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
