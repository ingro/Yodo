<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Comment extends Model
{
    use HasFactory;

    protected $table = 'comments';

    protected $fillable = ['post_id', 'username', 'content'];

    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id');
    }
}
