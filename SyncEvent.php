<?php

namespace chervand\sync;

use yii\base\Event;

class SyncEvent extends Event
{
    /**
     * @var BindingInterface
     */
    public $binding;
    /**
     * @var bool
     */
    public $isValid = true;
}
