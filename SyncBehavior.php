<?php
/**
 * @author chervand <chervand@gmail.com>
 */

namespace chervand\sync;

use Yii;
use yii\base\Behavior;
use yii\base\InvalidConfigException;
use yii\base\Model;

/**
 * Class SyncBehavior
 * @package chervand\sync
 */
class SyncBehavior extends Behavior
{
    const SCENARIO = 'sync';
    const DIRECTION_MODEL = 0;
    const DIRECTION_RELATED = 1;

    /**
     * @var array
     */
    public $bindings = [];


    /**
     * Synchronizes multiple models.
     * @param Model|Model[] $models models to synchronize
     * @param array $bindings bindings to apply, if empty all assigned bindings will be applied
     * @param callable|integer|null $direction sync direction, if null defined by binding
     * @return bool whether synchronization was successful or not
     * @throws InvalidConfigException
     * @see ensureBindings()
     */
    public static function syncMultiple($models, $bindings = [], $direction = null)
    {
        $models = is_array($models) ? $models : [$models];
        $bindings = is_array($bindings) ? $bindings : [$bindings];

        $synced = true;
        foreach ($models as $model) {
            if (!$model instanceof Model) {
                throw new InvalidConfigException(get_class($model) . ' is not an instance of Model.');
            }
            $synced = $synced && $model->sync($bindings, $direction);
        }

        return $synced;
    }

    /**
     * Synchronizes a model according to specified bindings.
     * @param array $bindings
     * @param callable|integer|null $direction
     * @return bool whether sync was successful or not
     * @see ensureBindings()
     */
    public function sync($bindings = [], $direction = null)
    {
        $bindings = $this->ensureBindings($bindings);

        $synced = true;
        foreach ($bindings as $binding) {
            if (isset($direction)) {
                $binding->direction = $direction;
            }
            $synced = $synced && $binding->sync();
        }

        return $synced;
    }

    /**
     * Ensures and returns bindings objects.
     * @param array $bindings if empty return all bindings, otherwise returns by binding ids.
     * If value is instance of binding it will be added to the result, so you can dynamically
     * attach bindings to the synchronization.
     * @return BindingInterface[]|array
     */
    protected function ensureBindings($bindings = [])
    {
        if (!$this->bindings && method_exists($this->owner, 'bindings')) {
            $this->bindings = $this->owner->bindings();
        }

        foreach ($this->bindings as $index => $value) {
            if (!$value instanceof BindingInterface) {
                if (!is_array($value)) {
                    $value = ['class' => $value];
                }
                $value = array_merge(['id' => $index, 'model' => &$this->owner], $value);
                $this->bindings[$value['id']] = Yii::createObject($value);
            }
        }

        if (empty($bindings)) {
            return $this->bindings;
        }

        foreach ($bindings as $index => $value) {
            if (!$value instanceof BindingInterface) {
                $bindings[$value] = $this->bindings[$value];
                unset($bindings[$index]);
            }
        }

        return $bindings;
    }
}
