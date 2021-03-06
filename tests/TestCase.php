<?php namespace Ingruz\Yodo\Test;

use App\Models\Post;
use App\Models\Comment;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\PostController;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Route;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

class TestCase extends OrchestraTestCase
{
    /**
     * Set up the test env
     */
    public function setUp(): void
    {
        parent::setUp();

        // $this->withFactories(__DIR__.'/factories');

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

        // $app['config']->set('yodo.repositoriesNamespace', 'App\\Data\\Repositories\\');

        // Route::model('post', Post::class);
    }

    protected function setUpDatabase($app)
    {
        $app['db']->connection()->getSchemaBuilder()->create('posts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->text('content');
            $table->integer('rating')->nullable();
            $table->string('author')->nullable();
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

        Post::factory()->count(20)->create([
            'rating' => 5
        ]);

        Post::factory()->count(80)->create();

        for ($i = 0; $i<200; $i++) {
            Comment::factory()->create([
                'post_id' => rand(1, 100)
            ]);
        }
    }

    protected function setUpRoutes()
    {
        Route::group(['middleware' => 'bindings'], function() {
            Route::resource('posts', PostController::class);
            Route::resource('comments', CommentController::class);
        });
    }
}
