<?php


namespace Commons\Contracts;


interface Sortable extends \Spatie\EloquentSortable\Sortable
{
    public function orderColumnName();
}