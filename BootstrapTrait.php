<?php

namespace maybeworks\libs;

trait BootstrapTrait
{
    protected $_traits;

    public function init()
    {
        parent::init();

        foreach ($this->traits() as $rc) {
            /** @var $rc \ReflectionClass */
            $methodName = $rc->getShortName() . 'Init';
            if ($rc->hasMethod($methodName)) {
                call_user_func([$this, $methodName]);
            }
        }
    }

    public function traits()
    {
        if (!$this->_traits) {
            $this->_traits = call_user_func_array(
                'array_merge',
                array_map(
                    function ($className) {
                        return (new \ReflectionClass($className))->getTraits() ?: [];
                    },
                    class_parents($this)
                )
            );
        }

        return $this->_traits;
    }
}