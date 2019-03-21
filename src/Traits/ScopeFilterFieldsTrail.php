<?php
/**
 * Filter Model fields
 *
 * @author: Pham Cong Toan
 * @date: 2018-08-14 09:39:58
 */
namespace PhpSoft\Base\Traits;

trait ScopeFilterFieldsTrail
{
    public $filterable = ['id'];

    public function scopeFilterFields($query, $params, $filterOptions = null)
    {
        if (empty($params)) {
            return $query;
        }
        $relationship = [];
        $onlyFilter = array_get($filterOptions, 'only');
        foreach ($params as $key => $value) {
            if (empty($value)) {
                continue;
            }
            $values = is_string($value) ? explode(',', $value) : $value;
            $options = null;
            if (strpos($key, '->$_')) {
                // parse json query
                $fields = explode('->$_', $key);
                $key = $fields[0];
                $options['path'] = $fields[1];
            }
            if (in_array($key, $this->fillable) || in_array($key, $this->filterable)) {
                $canFilter = true;
                if ($onlyFilter && !in_array($key, $onlyFilter)) {
                    $canFilter = false;
                }
                if ($canFilter) {
                    $this->filterField($query, $key, $values, $this->getTable(), $options);
                }
            } elseif (strpos($key, '.')) {
                $relate = explode('.', $key, 2);
                try {
                    // this verify a relatioship of model
                    $relation = $relate[0];
                    $this->$relation()->getRelated();
                } catch (\Exception $e) {
                    continue;
                }
                $relationship[$relate[0]][$relate[1]] = $values;
            } elseif (strpos($key, '_')) {
                $relate = explode('_', $key, 2);
                try {
                    // this verify a relatioship of model
                    $relation = $relate[0];
                    $this->$relation()->getRelated();
                } catch (\Exception $e) {
                    continue;
                }
                $relationship[$relate[0]][$relate[1]] = $values;
            }
        }
        foreach ($relationship as $name => $keys) {
            $query = $query->with($name);
            if ($this->$name() instanceof \Illuminate\Database\Eloquent\Relations\BelongsToMany) {
                $table = $this->$name()->getTable();
            } else {
                $table = $this->$name()->getRelated()->getTable();
            }
            $query->whereHas($name, function ($query) use ($keys, $table) {
                foreach ($keys as $key => $values) {
                    $this->filterField($query, $key, $values, $table);
                }
            });
        }

        $query->groupBy($this->getTable().'.'.$this->getKeyName());
        return $query;
    }

    private function filterField($query, $key, $values, $table = null, $options = null)
    {
        $values = array_filter(array_unique($values));
        if ($key == 'status' && $this->isBinaryStatus) {
            $query->statusWhere($values[0]);
            return $query;
        }
        if ($table) {
            $key = $table.'.'.$key;
        }

        if (count($values) == 1 && (starts_with($values[0], '%') || starts_with($values[0], '*'))) {
            $values[0] = str_replace('*', '%', $values[0]);
            $query->where($key, 'like', $values[0]);
            return $query;
        }

        $range = "/(?'operate'>=|<=|>|<|=|!)(?'value'.*)/";
        $multiRange = "/(?'operate'>=|<=|>|<|=|!)(?'value'.*),(?'operate2'>=|<=|>|<|=|!)(?'value2'.*)/";

        $rangeMatches = [];
        $multiRangeMatches = [];
        foreach ($values as $k => $value) {
            if (preg_match_all($multiRange, $value, $matches, PREG_SET_ORDER)) {
                // or mutil range like this: ?salon_rating[]=>=2,<3&salon_rating[]=>=4,<5
                $multiRangeMatches[] = $matches;
                unset($values[$k]);
            } elseif (preg_match_all($range, $value, $matches, PREG_SET_ORDER)) {
                // check if input format have only range like this
                // ?salon_rating=>=2,<=4
                // or salon_rating[]=>=2,<=4
                $rangeMatches[] = $matches;
                unset($values[$k]);
            }
        }
        if ($options) {
            $field = \DB::raw("{$key}->'$.{$options['path']}'");
            $query->where(function ($query) use ($field, $values) {
                foreach ($values as $value) {
                    if (is_int($value)) {
                        $query->orwhere(function ($query) use ($field, $value) {
                            $query->orwhere($field, \DB::raw($value));
                            $query->orwhere($field, \DB::raw("'$value'"));
                        });
                    } else {
                        if ($value === 'null') {
                            $query->orwhereNull($field);
                        } else {
                            $query->orwhere($field, $value);
                        }
                    }
                }
            });
            return $query;
        }
        if (!empty($rangeMatches)) {
            foreach ($rangeMatches as $range) {
                if ($range[0]['operate'] === '!') {
                    if ($range[0]['value'] === 'null') {
                        $query->whereNotNull($key);
                    } else {
                        $query->whereNotIn($key, [$range[0]['value']]);
                    }
                } else {
                    $query->where($key, $range[0]['operate'], $range[0]['value']);
                }
            }
        } elseif (!empty($multiRangeMatches)) {
            $query->where(function ($query) use ($key, $multiRangeMatches) {
                foreach ($multiRangeMatches as $ranges) {
                    $query->orWhere(function ($query) use ($key, $ranges) {
                        $query->where($key, $ranges[0]['operate'], $ranges[0]['value']);
                        $query->where($key, $ranges[0]['operate2'], $ranges[0]['value2']);
                    });
                }
            });
        }
        if (count($values)) {
            $query->where(function ($query) use ($key, $values) {
                foreach ($values as $value) {
                    if (starts_with($value, '%') || starts_with($value, '*')) {
                        $value = str_replace('*', '%', $value);
                        $query->where($key, 'like', $value);
                    } elseif ($value === 'null') {
                        $query->whereNull($key);
                    } elseif (count(explode(',', $value))) {
                        $query->whereIn($key, explode(',', $value));
                    } else {
                        $query->where($key, $value);
                    }
                }
            });
        }
    }

    /**
    * This determines the foreign key relations automatically to prevent the need to figure out the columns.
    *
    * @param \Illuminate\Database\Query\Builder $query
    * @param string $relation_name
    * @param string $operator
    * @param string $type
    * @param bool   $where
    * @return \Illuminate\Database\Query\Builder
    */
    public function scopeModelJoin($query, $relation_name, $operator = '=', $type = 'left', $where = false, $options = [])
    {
        $mainTable = $this->getTable();
        $relation = $this->$relation_name();
        $relationTable = $relation->getRelated()->getTable();
        $table = $relationTable.' as '.$relation_name;
        if ($relation instanceOf \Illuminate\Database\Eloquent\Relations\HasMany) {
            $one = $relation->getQualifiedParentKeyName();
            $two = $relation_name.'.'.$relation->getForeignKeyName();
            $relationWhere = $relation->getQuery()->getQuery()->wheres;
            foreach ($relationWhere as &$value) {
                $value['column'] = str_replace($relationTable.'.', $relation_name.'.', $value['column']);
                if (!strpos($value['column'], '.')) {
                    $value['column'] = $relation_name.'.'.$value['column'];
                }
            }
            unset($relationWhere[0]);
            unset($relationWhere[0]);
            $relation->getQuery()->getQuery()->wheres = $relationWhere;

            // this cause wrong where from relation to main query
            // $query->mergeConstraintsFrom($relation->getQuery());
        } else {
            $one = $relation->getQualifiedForeignKey();
            $two = $relation_name.'.'.$relation->getRelated()->getKeyName();
        }
        if (empty($query->columns)) {
            foreach (\Schema::getColumnListing($mainTable) as $column) {
                $query->addSelect(\DB::raw("`$mainTable`.`$column` AS `$column`"));
            }
        }
        if ($select = array_get($options, 'select')) {
            if (isset($relation->pivotColumns)) {
                $pivot = $relation->pivotColumns;
                foreach ($select as $column) {
                    $field = explode('.', $column)[1] ?? $column;
                    $pivotKey = $pivot[$field][0];
                    $pivotValue = $pivot[$field][1];
                    $query->addSelect(\DB::raw("coalesce(sum(case when $relation_name.$pivotKey = '$field' then $relation_name.$pivotValue end), 0) as `$column`"));
                }
            } else {
                foreach ($select as $column) {
                    $query->addSelect(\DB::raw("ANY_VALUE($column) AS `$column`"));
                }
            }
        } else {
            foreach (\Schema::getColumnListing($relationTable) as $column) {
                $query->addSelect(\DB::raw("ANY_VALUE(`$relation_name`.`$column`) AS `{$relation_name}.$column`"));
            }
        }

        return $query->join($table, function ($join) use ($one, $operator, $two, $type, $relation) {
            $join->on($one, $operator, $two);
            $join->mergeWheres($relation->getQuery()->getQuery()->wheres, $relation->getQuery()->getQuery()->getBindings());
        }, $operator, $two, $type);
    }
}
