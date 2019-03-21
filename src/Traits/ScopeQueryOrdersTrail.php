<?php
/**
 * Order by field
 *
 * @author: Pham Cong Toan
 * @date: 2018-08-14 09:42:08
 */
namespace PhpSoft\Base\Traits;

trait ScopeQueryOrdersTrail
{
    public $sortable = ['created_at', 'updated_at'];

    public function scopeQueryOrders($query, $cols, $prefix = null, $filterOptions = null)
    {
        $onlyFilter = array_get($filterOptions, 'only');
        $ordered = false;
        $canSort = true;
        foreach ($cols as $col) {
            $col = trim($col);
            if (substr($col, 0, 1) == '-') {
                $dir = 'desc';
                $col = substr($col, 1);
            } elseif (substr($col, 0, 1) == '+') {
                $dir = 'asc';
                $col = substr($col, 1);
            } else {
                $dir = 'asc';
            }

            $options = null;
            if (strpos($col, '->$.')) {
                // parse json query
                $fields = explode('->$.', $col);
                $col = $fields[0];
                $options['path'] = $fields[1];
            }
            if (in_array($col, $this->fillable) || $col == 'id' || in_array($col, $this->sortable)) {
                $canSort = true;
                if ($onlyFilter && !in_array($col, $onlyFilter)) {
                    $canSort = false;
                }
                if ($canSort) {
                    if ($prefix) {
                        $col = $prefix.'.'.$col;
                    }
                    if ($options) {
                        $query->orderBy(\DB::raw("{$col}->'$.{$options['path']}'"), $dir);
                    } else {
                        $query->orderBy(\DB::raw("ANY_VALUE($col)"), $dir);
                    }

                    $ordered = true;
                }
            } else {
                $relate = explode('.', $col, 2);
                if ($relate) {
                    try {
                        // this verify a relatioship of model
                        $relation = $relate[0];
                        $query->modelJoin($relation, '=', 'left', false, [
                            'select' => [$col]
                        ]);
                        $query->orderBy(\DB::raw("`$col`"), $dir);
                        $ordered = true;
                    } catch (\Exception $e) {
                        // dd($e);
                    }
                }
            }
        }

        if (!$ordered && $canSort) {
            $query->orderBy($this->getTable().'.id', 'desc');
        }

        $query->groupBy($this->getTable().'.'.$this->getKeyName());
        return $query;
    }
}
