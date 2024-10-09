<?php
/**
 * Created by PhpStorm.
 * User: adekhaha
 * Date: 12/01/19
 * Time: 11:59
 */

namespace Commons\Helpers;


use Commons\Helpers\Objects\InsertablesAndUpdatables;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class DatabaseHelpers
{

    const DEFAULT_PAGINATION_PER_PAGE = 100;

    /**
     * @param Collection $items
     * @param Model|string $model
     * @return InsertablesAndUpdatables
     */
    public static function getInsertablesAndUpdatables($items, $key, $model)
    {
        $itemsChunked = $items->chunk(1000);
        $nonExistingModelsChunked = [];
        $existingModelsChunked = [];
        foreach ($itemsChunked as $itemsChunk) {
            $itemsChunkCollected = collect($itemsChunk);
            $existingKeys = $itemsChunkCollected->map(function ($item) use ($key) {
                return data_get($item, $key);
            });
            $existingModels = $model::whereIn($key, $existingKeys->toArray())->get();
            $nonExistingModels = ArrayHelpers::diff($itemsChunkCollected, $existingModels, $key);
            $nonExistingModelsChunked[] = $nonExistingModels;
            $existingModelsChunked[] = $existingModels;
        }
        return new InsertablesAndUpdatables(collect($nonExistingModelsChunked), collect($existingModelsChunked));
    }

    /**
     * @param Model|Builder $model
     * @param $columns
     * @return Model|Builder
     */
    public static function addSearchQuery($model, $columns, $limit = true)
    {
        //to cater special character search with normal alphabets
//        \DB::statement('CREATE EXTENSION IF NOT EXISTS unaccent;');
        $request = \request();
        $searchInput = $request->get('search', '');
        $orderByKey = $request->get('orderByKey', '');
        $orderByType = $request->get('orderByType', '');

        $config = config('database.default');
        switch ($config) {
            case 'pgsql':
                $operator = 'ILIKE';
                break;
            default:
                $operator = 'LIKE';
                break;
        }

        if (strlen($orderByKey) === 0 || strlen($orderByType) === 0) {
            if ($limit) {
                $model = $model->limit(100);
            }
            if (strlen($searchInput) === 0) {
                return $model;
            }
            $searchString = '%' . $searchInput . '%';
            $model->where(function ($q) use ($searchString, $columns,$operator) {
                /** @var Builder $query */
                $query = $q;
                foreach ($columns as $column) {

                    $query = $query->where($column, $operator, $searchString, 'or');
//                    $query = $query->whereRaw("unaccent(" . $column . "::text) ilike unaccent('" . $searchString . "')", [], 'or');
                }
            });
        } else {
            $model = $model->orderBy($orderByKey, $orderByType);
            if ($limit) {
                $model = $model->limit(100);
            }
            if (strlen($searchInput) === 0) {
                return $model;
            }
            $searchString = '%' . $searchInput . '%';
            $model->where(function ($q) use ($searchString, $columns,$operator) {
                /** @var Builder $query */
                $query = $q;
                foreach ($columns as $column) {
                    $query = $query->where($column, $operator, $searchString, 'or');
//                    $query = $query->whereRaw("unaccent(" . $column . "::text) ilike unaccent('" . $searchString . "')", [], 'or');

                }
            })->orderBy($orderByKey, $orderByType);
        }
        return $model;
    }

    /**
     * @param Model|Builder $model
     * @param $defaultSortColumn
     * @param $customCols
     * @return Model|Builder
     */
    public static function addModelSortingQuery($model, $defaultSortColumn, $customCols)
    {
        $request = \request();
        $sortOrder = ($request->sort_order) ? $request->sort_order : 'desc';
        $sortBy = $request->get('sort_by', '');
        $column = (!empty($sortBy)) ? $sortBy : $defaultSortColumn;

        $sortColumn = array_get($customCols, $column, $column);
        $model = $model->orderBy($sortColumn, $sortOrder);

        return $model;
    }

    /**
     * @param Model|Builder $model
     * @param $relationAndColumns
     * @param $searchColumns
     * @return Model|Builder
     */
    public static function addModelSearchQuery($model, $relationAndColumns, $searchColumns)
    {
        $request = \request();
        $searchString = $request->get('search', '');

        if ($searchString) {
            if (!empty($relationAndColumns)) {
                foreach ($relationAndColumns as $relation => $columns) {
                    $queryType = $columns['query_type'];
                    $columns = $columns['col'];
                    $model = $model->$queryType($relation, function ($q) use ($searchString, $columns) {
                        /** @var Builder $query */
                        $query = $q;
                        $i = 0;
                        foreach ($columns as $col) {
                            if ($i == 0) {
                                $query = $query->where($col, 'LIKE', "%$searchString%");
                            } else {
                                $query = $query->where($col, 'LIKE', "%$searchString%", 'or');
                            }
                            $i++;
                        }
                    });
                }
            }
            if (!empty($searchColumns)) {
                $model = $model->where(function ($q) use ($searchString, $searchColumns) {
                    /** @var Builder $query */
                    $query = $q;
                    $j = 0;
                    foreach ($searchColumns as $col) {
                        if ($j == 0) {
                            $query = $query->where($col, 'LIKE', "%$searchString%");
                        } else {
                            $query = $query->where($col, 'LIKE', "%$searchString%", 'or');
                        }
                        $j++;
                    }
                });
            }
        }
        return $model;
    }

    /**
     * @param Model|Builder $model
     * @return Model|Builder
     */
    public static function addModelPaginationQuery($model)
    {
        $request = \request();
        $paginationValue = ($request->per_page) ? $request->per_page : self::DEFAULT_PAGINATION_PER_PAGE;
        $model = $model->paginate($paginationValue);
        return $model;
    }

    public static function generateInsertBindingHolders($columnCount, $rowCount = 1)
    {
        $columnBindingFields = [];
        for ($i = 0; $i < $columnCount; $i++) {
            $columnBindingFields[] = '?';
        }
        $columnBinding = "(" . implode(',', $columnBindingFields) . ")";
        $rowBindingFields = [];
        for ($i = 0; $i < $rowCount; $i++) {
            $rowBindingFields[] = $columnBinding;
        }
        $rowBinding = implode(',', $rowBindingFields);
        return $rowBinding;
    }

    public static function modelBasedFilter($model,$filterValues)
    {
        /*
        This function handles Multiple filters with Multi select Options.
        Key of this Array should be column name of database table it also can be from relationship table
        Sample Format of $filterValues :
        [
          "job_band_code" => array(1,2,3),
          "job_family_id" => array(64)
        ]
        */
        foreach ($filterValues as $column_name => $column_value) {
            $model = $model->whereIn($column_name, $column_value);
        }
        return $model;
    }

}
