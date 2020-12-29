<?php namespace App\Transformers;

use App\Models\Post;
use League\Fractal\TransformerAbstract;

class PostTransformer extends TransformerAbstract
{
    protected $defaultIncludes = [
        'comments'
    ];

    public function transform(Post $item)
    {
        return [
            'id' => $item->id,
            'title' => $item->title,
            'content' => $item->content,
            'rating' => $item->rating,
            'comments_number' => $item->comments->count()
        ];
    }

    public function includeComments(Post $item)
    {
        return $this->collection($item->comments, new CommentTransformer);
    }
}
