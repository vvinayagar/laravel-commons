<?php


namespace Commons\Traits;

use Commons\Contracts\Sortable;
use Illuminate\Http\Request;

trait HasSortAction
{
    protected function orderSortable(Request $request, Sortable $sortable)
    {
        $columnName = $sortable->orderColumnName();
        
        $request->validate([
            $columnName => 'required|integer'
        ]);

        $sortable->$columnName = $request->$columnName;
        
        $sortable->save();
        
        return $sortable;
    }
}