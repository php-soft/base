<?php

namespace PhpSoft\Base;

class FactoryViewHelper extends \ChickenCoder\ArrayView\Factory
{
    public function partial($partialView, $data = [], $mergeData = [])
    {
        $factory = $this->getFactory();
        if ($id = array_get($data, str_after($partialView, '/').'.id')) {
            $GLOBALS['view_partial'][$partialView.':'.$id] = $data;
        }

        /**
         * we find a identify to cache view
         */
        return $factory->render($partialView, $data, $mergeData);
    }

    /**
     * Get factory
     * @return Factory
     */
    private function getFactory()
    {
        $factory = new self($this->viewPaths, $this->finder);
        return $factory;
    }
}
