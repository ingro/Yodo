<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Post extends Model
{
    use HasFactory;

    protected $table = 'posts';

    protected $fillable = ['title', 'content', 'rating'];

    static $rules = [
        'save' => [
            'title' => 'required'
        ]
    ];

    public function comments()
    {
        return $this->hasMany(Comment::class, 'post_id');
    }

    public function scopeWithTopRating($query)
    {
        return $query->where('rating', '5');
    }
}
