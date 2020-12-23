<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Post extends Model
{
    use HasFactory;

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
