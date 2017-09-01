<?php namespace Ingruz\Yodo\Test;

use App\Post;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ControllerTest extends TestCase
{
    protected $itemJsonStructure = [
        'id',
        'title',
        'content',
        'comments_number',
        'comments'
    ];

    public function testIndexRoute()
    {
        $response = $this->json('GET', 'posts?limit=5');

        $response->assertSuccessful();
        $response->assertJsonStructure([
            'data' => [
                '*' => $this->itemJsonStructure
            ],
            'meta' => [
                'pagination' => [
                    'total',
                    'count',
                    'per_page',
                    'current_page',
                    'total_pages',
                    'links'
                ]
            ]
        ]);

        $json = $response->decodeResponseJson();

        $this->assertCount(5, $json['data']);
        $this->assertEquals(100, $json['meta']['pagination']['total']);
        $this->assertEquals(5, $json['meta']['pagination']['count']);
        $this->assertEquals(5, $json['meta']['pagination']['per_page']);
        $this->assertEquals(1, $json['meta']['pagination']['current_page']);
        $this->assertEquals(20, $json['meta']['pagination']['total_pages']);
    }

    public function testShowRoute()
    {
        $response  = $this->json('GET', 'posts/37');

        $response->assertSuccessful();
        $response->assertJsonStructure($this->itemJsonStructure);

        $json = $response->decodeResponseJson();

        $this->assertEquals(37, $json['id']);

        $responseNotFound = $this->json('GET', 'posts/999');

        $responseNotFound->assertStatus(404);
    }

    public function testStoreRoute()
    {
        $title = 'My shiny new post';

        $response = $this->json('POST', 'posts', [
            'title' => $title,
            'content' => 'Ipsum Lorem'
        ]);

        $response->assertSuccessful();
        $response->assertJsonStructure($this->itemJsonStructure);

        $json = $response->decodeResponseJson();

        $this->assertEquals(101, $json['id']);
        $this->assertEquals($title, $json['title']);

        $post = Post::findOrFail(101);

        $this->assertEquals($title, $post->title);
    }

    public function testUpdateRoute()
    {
        $title = 'This is way better';

        $response = $this->json('PATCH', 'posts/45', [
            'title' => $title
        ]);

        $response->assertSuccessful();
        $response->assertJsonStructure($this->itemJsonStructure);

        $json = $response->decodeResponseJson();

        $this->assertEquals(45, $json['id']);
        $this->assertEquals($title, $json['title']);

        $post = Post::findOrFail(45);

        $this->assertEquals($title, $post->title);
    }

    public function testFailedUpdateRoute()
    {
        // Fails validation
        $response = $this->json('PATCH', 'posts/45', [
            'content' => 'Some content'
        ]);

        $response->assertStatus(500);
    }

    public function testDeleteRoute()
    {
        $response = $this->json('DELETE', 'posts/12');

        $response->assertSuccessful();
        $response->assertExactJson(['result' => 'ok']);

        $this->expectException(ModelNotFoundException::class);

        Post::findOrFail(12);
    }

    public function testApiLimitValidation()
    {
        $response = $this->json('GET', 'posts?limit=500');

        $response->assertStatus(500);
    }
}
