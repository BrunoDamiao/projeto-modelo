<?php

namespace App\Filters;

use FwBD\Filter\BaseFilter;
use FwBD\Controller\BaseController;
use FwBD\DI\Container as Container;

/**
*
*/
class SessionFilter extends BaseFilter
{

    public function Filter($value='')
    {
        echo "sessao filtro: middleware de teste";

    }

}