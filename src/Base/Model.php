<?php namespace Ingruz\Yodo\Base;


class Model extends \Illuminate\Database\Eloquent\Model
{
    /**
     * Setup the model events
     */
    public static function boot()
    {
        parent::boot();

        self::saving(function($model)
        {
            return $model->beforeSave();
        });

        self::saved(function($model)
        {
            return $model->afterSave();
        });

        self::deleting(function($model)
        {
            return $model->beforeDelete();
        });

        self::deleted(function($model)
        {
            return $model->afterDelete();
        });
    }

    /**
     * Action to be executed before the model is saved on the database
     *
     * @return bool
     */
    protected function beforeSave()
    {
        return true;
    }

    /**
     * Action to be executed after the model is saved on the database
     *
     * @return bool
     */
    protected function afterSave()
    {
        return true;
    }

    /**
     * Action to be executed before the model will be deleted from the database
     *
     * @return bool
     */
    protected function beforeDelete()
    {
        return true;
    }

    /**
     * Action to be executed after the model has been deleted from the database
     *
     * @return bool
     */
    protected function afterDelete()
    {
        return true;
    }
}
