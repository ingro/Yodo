<?php

namespace Ingruz\Yodo\Test;

use Ingruz\Yodo\Base\Model;

class TestModel extends Model
{
    protected $table = 'posts';

    protected $fillable = ['title', 'content'];
}
