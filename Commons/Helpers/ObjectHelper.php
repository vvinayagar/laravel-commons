<?php


namespace Commons\Helpers;


class ObjectHelper
{
    /**
     * Remove all other fields that doesn't exist.
     *
     * @param $target
     * @param $filterObject
     */
    public static function removeOtherFields($target, $filterObject, $extraFieldsToRemove = [])
    {
        $keys = array_keys((array)$filterObject);
        $keysFiltered = array_filter(array_keys((array)$filterObject), function ($value) use ($extraFieldsToRemove) {
            return array_search($value, $extraFieldsToRemove) === false;
        });
        $obj = new \stdClass();
        collect($keysFiltered)->each(function ($key) use (&$obj, &$target) {
            if (isset($target->$key)) {
                $obj->$key = $target->$key;
            }
        });
        return $obj;
    }

    /**
     * Copy keys from source to target, only if it exists.
     * @param $target
     * @param $source
     */
    public static function shallowCopy($target, $source, $excludedKey = [])
    {
        $keys = array_keys((array)$source);
        collect($keys)->each(function ($key) use (&$target, $source, &$excludedKey) {
            if (!in_array($key, $excludedKey)) {
                $target->$key = $source->$key;
            }
        });
        return $target;
    }


    /**
     * Copy the value of fields from source to target, regardless if it exists or not.
     *
     * @param $target
     * @param $source
     * @param $fields
     */
    public static function copyFields(&$target, &$source, $fields)
    {
        foreach ($fields as $field) {
            $target[$field] = $source[$field];
        }
        return $target;
    }

    /**
     * @param $object
     * @param string $currentKey
     * @return \Illuminate\Support\Collection
     */
    public static function flattenObjectKeysIntoPaths($object, $currentKey = '')
    {
        $flattened = collect((array)$object)->flatMap(function ($value, $key) use ($currentKey, $object) {
            $newKey = strlen($currentKey) === 0 ? $key : $currentKey . '.' . $key;
            if (is_array($value) || is_object($value)) {
                // if it's an array, add the current key and recurse.
                return self::flattenObjectKeysIntoPaths($value, $newKey)->push($newKey);
            }
            // else, return the key without flattening it.
            return [$newKey];
        });
        return $flattened;
    }
}
