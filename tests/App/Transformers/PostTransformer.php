<?php namespace App\Transformers;

use App\Post;
use League\Fractal\TransformerAbstract;

class PostTransformer extends TransformerAbstract
{
    public function transform(Post $item)
    {
        return [
            'id' => $item->id,
            'title' => $item->title,
            'content' => $item->content
        ];
    }
}
