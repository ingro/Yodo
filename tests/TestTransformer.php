<?php namespace Ingruz\Yodo\Test;

use League\Fractal\TransformerAbstract;

class TestTransformer extends TransformerAbstract
{
    public function transform(TestModel $item)
    {
        return [
            'id' => $item->id,
            'title' => $item->title,
            'content' => $item->content
        ];
    }
}
