<?php namespace App\Http\Controllers;

use App\Post;
use App\Repositories\PostRepository;
use App\Transformers\PostTransformer;
use Ingruz\Yodo\Base\Controller;

class PostController extends Controller
{
    protected $transformerClass = PostTransformer::class;

    /**
     * PostController constructor.
     * @param PostRepository $repository
     * @param Post $model
     */
    public function __construct(PostRepository $repository, Post $model)
    {
        parent::__construct($repository, $model);
    }
}
