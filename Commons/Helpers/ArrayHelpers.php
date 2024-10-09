<?php

namespace Commons\Helpers;


class ArrayHelpers
{
    /**
     * https://stackoverflow.com/a/6700430
     * @param  $a
     * @param $b
     * @param null $key
     * @return array
     */
    public static function diff($a, $b, $key = null) {
        $collectionA = collect($a);
        $collectionAKeyed = [];
        $collectionB = collect($b);
        $collectionBKeyed = [];
        if (!is_null($key)) {
            $collectionA->each(function ($item) use ($key, &$collectionAKeyed) {
                $collectionAKeyed[data_get($item, $key)] = $item;
            });

            $collectionB->each(function ($item) use ($key, &$collectionBKeyed) {
                $collectionBKeyed[data_get($item, $key)] = $item;
            });
        } else {
            $collectionAKeyed = $collectionA->toArray();
            $collectionBKeyed = $collectionB->toArray();
        }
        $map = [];
        foreach($collectionAKeyed as $key => $val) {
            if (!is_null($key)) {
                $map[$key] = 1;
            } else {
                $map[$val] = 1;
            }
        }

        foreach($collectionBKeyed as $key => $val) {
            if (!is_null($key)) {
                unset($map[$key]);
            } else {
                unset($map[$val]);
            }
        }

        $values = [];
        if (!is_null($key)) {
            foreach ($map as $diffKey => $nothing) {
                $values[] = $collectionAKeyed[$diffKey];
            }
        } else {
            $values = array_keys($map);
        }
        return $values;
    }
}
