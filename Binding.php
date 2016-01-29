<?php

namespace chervand\sync;

use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\base\Model;

/**
 * Class Binding implements a sync logic between two entities.
 * @package chervand\sync
 */
abstract class Binding extends Component implements BindingInterface
{
    const DIRECTION_MODEL = 0;
    const DIRECTION_RELATED = 1;
    /**
     * @var string
     */
    public $id;
    /**
     * @var \yii\base\Model
     */
    public $model;
    /**
     * @var callable|null
     */
    public $sync;
    /**
     * @var callable|integer|null
     */
    public $direction;
    /**
     * @var callable|null
     */
    public $attributes;
    /**
     * @var callable|null
     */
    public $commit;


    /**
     * Init.
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();

        if (!isset($this->id)) {
            throw new InvalidConfigException(get_class($this) . '::$id');
        }

        if (!$this->model instanceof Model) {
            throw new InvalidConfigException(get_class($this) . '::$model');
        }

        $this->model->setScenario('sync');
    }

    /**
     * @inheritdoc
     */
    public function sync()
    {
        if ($this->sync instanceof \Closure) {
            return call_user_func($this->sync, $this);
        }

        return $this->syncInternal();
    }

    /**
     * @return bool
     */
    protected abstract function syncInternal();


    public function direction(&$model, &$related)
    {
        if (is_callable($this->direction)) {
            return call_user_func($this->direction, $model);
        }
        if (in_array($this->direction, [static::DIRECTION_MODEL, static::DIRECTION_RELATED])) {
            return $this->direction;
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function attributes(&$changed)
    {
        if (is_callable($this->attributes)) {
            return call_user_func($this->attributes, $changed);
        }

        return $this->attributesInternal($changed);
    }

    /**
     * @param \yii\base\Model $changed
     * @return array
     */
    protected abstract function attributesInternal($changed);

    /**
     * @inheritdoc
     */
    public function commit(&$model, &$attributes)
    {
        if ($this->commit instanceof \Closure) {
            return call_user_func($this->commit, $model, $attributes);
        }

        return $this->commitInternal($model, $attributes);
    }

    /**
     * @param \yii\base\Model $model
     * @param array $attributes
     * @return bool
     */
    protected abstract function commitInternal($model, $attributes);
}
