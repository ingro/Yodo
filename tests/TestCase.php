<?php namespace Ingruz\Yodo\Test;

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
    }

    protected function setUpDatabase($app)
    {
        $app['db']->connection()->getSchemaBuilder()->create('posts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->text('content');
            $table->timestamps();
        });

        for($i = 0; $i < 100; $i++)
        {
            factory(TestModel::class)->create();
        }
    }

    protected function setUpRoutes()
    {
        Route::resource('api/posts', TestController::class);
    }
}
