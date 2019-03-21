<?php

namespace PhpSoft\Base\Models;

use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
    use \PhpSoft\Base\Traits\BaseModelTrait;

    protected $casts = [
        'id' => 'string',
    ];
}
