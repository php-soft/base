<?php

namespace PhpSoft\Base\Traits;

trait AuthTrait
{
    public function userModel()
    {
        return config('auth.providers.users.model');
    }
}
