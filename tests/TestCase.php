<?php namespace Ingruz\Yodo\Test;

use App\Comment;
use App\Post;
use App\Http\Controllers\PostController;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Route;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

class TestCase extends OrchestraTestCase
{
    /**
     * Set up the test env
     */
    public function setUp()
    {
        parent::setUp();

        $this->withFactories(__DIR__.'/factories');

        $this->setUpDatabase($this->app);

        $this->setUpRoutes();
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('app.debug', true);

        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // Route::model('post', Post::class);
    }

    protected function setUpDatabase($app)
    {
        $app['db']->connection()->getSchemaBuilder()->create('posts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->text('content');
            $table->timestamps();
        });

        $app['db']->connection()->getSchemaBuilder()->create('comments', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('post_id')->unsigned();
            $table->string('username');
            $table->text('content');
            $table->timestamps();

            $table->foreign('post_id')->references('id')->on('post');
        });

        for($i = 0; $i < 100; $i++) {
            factory(Post::class)->create();
        }

        for($i = 0; $i<200; $i++) {
            factory(Comment::class)->create([
                'post_id' => rand(1, 100)
            ]);
        }
    }

    protected function setUpRoutes()
    {
        Route::group(['middleware' => 'bindings'], function() {
            Route::resource('posts', PostController::class);
        });
    }
}
