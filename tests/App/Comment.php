<?php namespace App;

use Ingruz\Yodo\Base\Model;

class Comment extends Model
{
    protected $table = 'comments';

    protected $fillable = ['post_id', 'username', 'content'];

    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id');
    }
}
