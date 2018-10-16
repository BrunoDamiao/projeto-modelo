<?php

namespace App\Filters;

use FwBD\Filter\BaseFilter;
use FwBD\Controller\BaseController;
use FwBD\DI\Container as Container;

/**
*
*/
class middlewareTestex extends BaseFilter
{

    public function Filter($value='')
    {
        echo "middleware de teste x >>>>>>>>>>";
        pp($value);
    }

}