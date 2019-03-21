<?php
/**
 * Filter status bitwise
 *
 * @author: Pham Cong Toan
 * @date: 2018-08-14 09:41:38
 */
namespace PhpSoft\Base\Traits;

trait ScopeFilterStatusTrail
{
    public function scopeStatusWhere($query, $value)
    {
        if ($value == null) {
            return $query;
        }
        if (!$this->isBinaryStatus) {
            return $query->where('status', $value);
        }

        $value = explode('!', $value);
        if (count($value) == 1) {
            return $query->whereRaw($this->getTable().'.status & ?', [$value[0]]);
        } else {
            return $query->whereRaw('not '.$this->getTable().'.status & ?', [$value[1]]);
        }
    }
}
