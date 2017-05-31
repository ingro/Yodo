<?php namespace App;

use Ingruz\Yodo\Base\Model;

class Post extends Model
{
    protected $table = 'posts';

    protected $fillable = ['title', 'content'];

    public function comments()
    {
        return $this->hasMany(Comment::class, 'post_id');
    }
}
