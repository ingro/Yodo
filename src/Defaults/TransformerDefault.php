<?php namespace Ingruz\Yodo\Defaults;

use Illuminate\Database\Eloquent\Model;
use League\Fractal\TransformerAbstract;

class TransformerDefault extends TransformerAbstract
{
    /**
     * @param Model $item
     * @return array
     */
    public function transform(Model $item)
    {
        return $item->toArray();
    }
}
