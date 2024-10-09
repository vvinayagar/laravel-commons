<?php
/**
 * Created by PhpStorm.
 * User: adekhaha
 * Date: 12/01/19
 * Time: 12:00
 */

namespace Commons\Helpers\Objects;


use Illuminate\Support\Collection;

class InsertablesAndUpdatables
{
    /**
     * @var Collection
     */
    public $insertables;
    /**
     * @var Collection
     */
    public $updatables;
    public function __construct($insertables, $updatables)
    {
        $this->insertables = $insertables;
        $this->updatables = $updatables;
    }
}
