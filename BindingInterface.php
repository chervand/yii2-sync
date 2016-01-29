<?php
/**
 * @author chervand <chervand@gmail.com>
 */

namespace chervand\sync;

/**
 * Interface BindingInterface
 * @package chervand\sync
 *
 * @property string $id
 * @property \yii\base\Model $model
 * @property callable|null $sync
 * @property callable|integer|null $direction
 * @property callable|null $attributes
 * @property callable|null $commit
 */
interface BindingInterface
{
    /**
     * @return boolean
     */
    public function sync();

    /**
     * @param \yii\base\Model $changed
     * @return array
     */
    public function attributes(&$changed);

    /**
     * @param \yii\base\Model $model
     * @param $attributes
     * @return boolean
     */
    public function commit(&$model, &$attributes);

    /**
     * @param \yii\base\Model $model
     * @param \yii\base\Model $related
     * @return integer|null
     */
    public function direction(&$model, &$related);
}
