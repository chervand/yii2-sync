<?php

namespace chervand\sync;

use yii\base\InvalidConfigException;
use yii\db\ActiveRecord;
use yii\db\Connection;

/**
 * Class ActiveBinding
 * @package chervand\sync
 */
class ActiveBinding extends Binding
{
    /**
     * @var \yii\db\ActiveRecord
     */
    public $model;
    /**
     * @var string[]
     */
    public $relations = [];
    /**
     * @var \yii\db\Connection
     */
    public $relationsDb;
    /**
     * @var bool
     */
    public $transactional = false;
    /**
     * @var integer|null
     */
    public $relatedCacheDuration = 3600;


    public function init()
    {
        parent::init();

        if (!$this->relationsDb instanceof Connection) {
            $this->transactional = false;
            $this->relatedCacheDuration = null;
        }
    }

    /**
     * @return bool
     * @throws InvalidConfigException
     */
    protected function syncInternal()
    {
        if (!$this->model instanceof ActiveRecord) {
            throw new InvalidConfigException();
        }

        $synced = true;
        $model = &$this->model;
        $transaction = $this->transactional === true ? $this->relationsDb->beginTransaction() : null;
        try {
            foreach ($this->relations as $relation) {
                if (isset($this->relatedCacheDuration)) {
                    $related = $this->relationsDb->cache(function () use ($model, $relation) {
                        return $model->$relation;
                    }, $this->relatedCacheDuration);
                } else {
                    $related = $model->$relation;
                }
                $related->setScenario('sync');
                switch ($this->direction($model, $related)) {
                    case static::DIRECTION_MODEL:
                        $attributes = $this->attributes($related);
                        $synced = $synced && $this->commit($model, $attributes);
                        break;
                    case static::DIRECTION_RELATED:
                        $attributes = $this->attributes($model);
                        $synced = $synced && $this->commit($related, $attributes);
                        break;
                }
            }
            if ($this->transactional === true) {
                $transaction->commit();
            }
        } catch (\Exception $e) {
            if ($this->transactional === true) {
                $transaction->rollBack();
            }
            $synced = false;
        }

        return $synced;
    }

    /**
     * @param \yii\db\ActiveRecord $changed
     * @return array
     */
    protected function attributesInternal($changed)
    {
        return $changed->getDirtyAttributes();
    }

    /**
     * @param \yii\db\ActiveRecord $model
     * @param array $attributes
     * @return bool
     */
    protected function commitInternal($model, $attributes)
    {
        $model->setAttributes($attributes);
        return $model->save(false, array_keys($attributes));
    }
}
