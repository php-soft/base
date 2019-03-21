<?php

namespace PhpSoft\Base\Traits;

trait BaseModelTrait
{
    use DateTrait;
    use ScopeFilterFieldsTrail;
    use ScopeFilterStatusTrail;
    use ScopeQueryOrdersTrail;
    use \Bean\Activity\Traits\Activitiable;

    public function fireModelEvent($event, $halt = true)
    {
        return parent::fireModelEvent($event, $halt);
    }
}
