<?php


namespace Commons\Models\Traits;

use Commons\Contracts\Sortable;

trait SortableTrait
{
    use \Spatie\EloquentSortable\SortableTrait {
        bootSortableTrait as disabledBootSortableTrait;
        buildSortQuery as baseBuildSortQuery;
    }

    public static function bootSortableTrait()
    {
        static::saving(function ($m) {
            /** @var SortableTrait $model */
            $model = $m;
            if ($model instanceof Sortable) {
                $columnName = $model->determineOrderColumnName();
                if (!isset($model->$columnName)) {
                    $model->setHighestOrderNumber();
                } elseif ($model->isDirty($columnName)) {
                    $model->reorder();
                }
            }
        });
    }
    
    public function reorder()
    {
        $orderColumnName = $this->determineOrderColumnName();

        $keyName = $this->getKeyName();

        if ((bool)$this->buildSortQuery()
            ->where($orderColumnName, '=', $this->$orderColumnName)
            ->first()) {
            $this->buildSortQuery()
                ->where($keyName, '!=', $this->$keyName)
                ->where($orderColumnName, '>=', $this->$orderColumnName)
                ->increment($orderColumnName);
        }
        
        
    }

    public function orderColumnName()
    {
        return $this->determineOrderColumnName();
    }

    protected function orderGroup()
    {
        if (
            isset($this->sortable['order_group']) &&
            ! empty($this->sortable['order_group'])
        ) {
            return $this->sortable['order_group'];
        }

        return null;
    }


    /**
     * Build eloquent builder of sortable.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function buildSortQuery()
    {
        if ($group = $this->orderGroup()) {
            $groupValue = $this->$group;

            return static::baseBuildSortQuery()->where($group, $groupValue);    
        }
        
        return static::baseBuildSortQuery();
    }
}
