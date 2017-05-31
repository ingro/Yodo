<?php namespace App;

use Ingruz\Yodo\Base\Model;

class Post extends Model
{
    protected $table = 'posts';

    protected $fillable = ['title', 'content'];
}
