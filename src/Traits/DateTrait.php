<?php

namespace PhpSoft\Base\Traits;

trait DateTrait
{
    /**
    * Update created_at
    * @param array attributes
    * @return string
    */
    public function getCreatedAttribute()
    {
        return $this->created_at ? $this->created_at->toIso8601String() : '';
    }

    /**
    * Update update_at
    * @param array attributes
    * @return string
    */
    public function getUpdatedAttribute()
    {
        return $this->updated_at ? $this->updated_at->toIso8601String() : '';
    }
}
