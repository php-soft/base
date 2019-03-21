<?php

namespace PhpSoft\Base\Traits;

trait SwapSortTrait
{
    public function swapSort($model) {
        $tmp = $this->sort;
        $this->sort = $model->sort;
        $model->sort = $tmp;
        $this->save();
        $model->save();
    }
}
