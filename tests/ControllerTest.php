<?php namespace Ingruz\Yodo\Test;

class ControllerTest extends TestCase
{
    public function testIndexRoute()
    {
        $response = $this->json('GET', 'posts?limit=5');

//        dump($response->dump());

        $response->assertSuccessful();
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'title',
                    'content',
                    'comments_number',
                    'comments'
                ]
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

    /*public function testShowRoute()
    {
        $response = $this->json('GET', 'posts/999');

        dump($response->dump());

        $response->assertStatus(404);
    }*/
}
