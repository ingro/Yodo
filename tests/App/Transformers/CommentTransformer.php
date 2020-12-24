<?php namespace App\Transformers;

use App\Models\Comment;
use League\Fractal\TransformerAbstract;

class CommentTransformer extends TransformerAbstract
{
    public function transform(Comment $item)
    {
        return [
            'id' => $item->id,
            'post_id' => $item->post_id,
            'username' => $item->username,
            'content' => $item->content
        ];
    }
}
