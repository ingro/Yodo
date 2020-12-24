<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostWithEvents extends Model
{
    protected $table = 'posts';

    protected $fillable = ['title', 'content'];

    static $rules = [
        'save' => [
            'title' => 'required'
        ]
    ];

    public function comments()
    {
        return $this->hasMany(Comment::class, 'post_id');
    }
}
