<?php
namespace PhpSoft\Base\Repositories\Eloquent;

use PhpSoft\Base\Repositories\Contracts\BreadInterface;

abstract class BreadRepository implements BreadInterface
{
    public function browse($filter = [], $options = [])
    {
        $filter = $filter ?: request()->query();
        if (array_get($options, 'isMergeFilter', true)) {
            request()->merge($filter);
        }
        if (isset($options['query'])) {
            request()->merge($options['query']);
            // $filter = array_merge($filter, $options['query']);
        }
        $size = array_get($options, 'page.size', request()->input('page.size', 10));
        $number = array_get($options, 'page.number', request()->input('page.number', 1));
        $sort = explode(',', request()->input('sort', array_get($options, 'sort', '-id')));
        unset($filter['sort']);

        $model = $this->getModel();
        $query = $model::queryOrders($sort)->filterFields($filter);

        // @TODO: This is for filter whereNotIN. Need move it to Base BREAD
        if (isset($options['notIn'])) {
            $keys = array_keys($options['notIn']);
            $query->whereNotIn($keys[0], $options['notIn'][$keys[0]]);
        }

        if (isset($options['orNotNull'])) {
            $query->where(function ($query) use ($options) {
                foreach ($options['orNotNull'] as $value) {
                    $query->orWhereNotNull($value);
                }
            });
        }

        // add a custom builder
        if (isset($options['builder']) && is_callable($options['builder'])) {
            $options['builder']($query);
        }
        // dump($query->toSql(), str_replace_array('?', $query->getBindings(), $query->toSql()));
        // dump($query->toSql());
        return $query->paginate($size, ['*'], 'page[number]', $number);
    }

    public function read($id, $filter = [], $options = [])
    {
        $model = $this->getModel();
        $id && $filter['id'] = $id;
        $get = array_get($options, 'get', 'firstOrFail');
        return $this->getModel()::where($filter)->$get();
    }

    public function edit($id, $data)
    {
        $entity = $this->read($id);
        $data = $this->parseData($data, true);
        $entity->update($data);
        $entity->fireModelEvent('updated', false);
        return $this->read($id);
    }

    public function add($data)
    {
        $data = $this->parseData($data);
        return $this->getModel()::create($data);
    }

    public function delete($id)
    {
        $model = $this->getModel();
        if (is_array($id)) {
            $delete = $model::whereIn('id', $id)->get()->each(function($item) {
                $item->delete();
            });
            return true;
        }
        return $model::find($id)->delete();
    }

    public function update($filter, $data)
    {
        $model = $this->getModel();
        $result = $model::where($filter)->update($data);
        /**
         * For unknown reasons we throw if update fail
         */
        $result || throwUnknownException('Update ' . get_class_name($model) . ' is failed');
        return $result;
    }

    public function parseData($data, $isEdit = false)
    {
        return $data;
    }
}
