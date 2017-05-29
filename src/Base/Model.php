<?php namespace Ingruz\Yodo\Base;

use Ingruz\Yodo\Exceptions\ModelValidationException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\MessageBag;

class Model extends \Illuminate\Database\Eloquent\Model
{
    /**
     * @var array
     */
    static $rules = [
        'save' => [],
        'create' => [],
        'update' => []
    ];

    /**
     * @var bool
     */
    protected $saved = false;

    /**
     * @var bool
     */
    protected $valid = false;

    /**
     * @var MessageBag
     */
    protected $validationErrors;

    /**
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->validationErrors = new MessageBag;
    }

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

    /**
     * Return the model validity
     *
     * @return boolean
     */
    public function isValid()
    {
        return $this->valid;
    }

    /**
     * Check if the model has been save
     *
     * @return boolean
     */
    public function isSaved()
    {
        return $this->saved;
    }

    /**
     * Persist the model to the DB if it's valid
     *
     * @param  array   $options
     * @param  boolean $force
     * @return boolean
     */
    public function save(array $options = [], $force = false)
    {
        if ($force || $this->validate())
        {
            return $this->performSave($options);
        } else
        {
            return false;
        }
    }

    /**
     * Save the model on the database
     *
     * @param  array $options
     * @return boolean
     */
    protected function performSave(array $options = []) {
        $this->saved = true;
        return parent::save($options);
    }

    /**
     * Validate the model by the defined rules
     *
     * @throws ModelValidationException
     * @return boolean
     */
    protected function validate()
    {
        $op = $this->exists ? 'update' : 'create';
        $rules = $this->getValidationRules($op);

        if (empty($rules))
        {
            return true;
        }

        $data = $this->attributes;
        $validator = Validator::make($data, $rules);
        $result = $validator->passes();

        if ($result)
        {
            if ($this->validationErrors->count() > 0)
            {
                $this->validationErrors = new MessageBag;
            }
        } else
        {
            $this->validationErrors = $validator->messages();
            throw new ModelValidationException($validator->messages());
        }

        $this->valid = true;

        return $result;
    }
    
    /**
     * Return a single array with the rules for the action required
     *
     * @param string $op
     * @return array
     */
    public function getValidationRules($op = 'update')
    {
        $rules = static::$rules;
        $output = [];

        if (empty ($rules))
        {
            return $output;
        }

        if ($op === 'update')
        {
            $merged = (isset($rules['update'])) ? array_merge_recursive($rules['save'], $rules['update']) : $rules['save'];
        } else
        {
            $merged = (isset($rules['create'])) ? array_merge_recursive($rules['save'], $rules['create']) : $rules['save'];
        }

        foreach ($merged as $field => $rules)
        {
            if (is_array($rules))
            {
                $output[$field] = implode("|", $rules);
            } else
            {
                $output[$field] = $rules;
            }
        }

        return $output;
    }
}
