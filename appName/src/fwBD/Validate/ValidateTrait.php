<?php
namespace FwBD\Validate;

use FwBD\DI\Container;

trait ValidateTrait
{

    public function getUniqueDB($model, $fields, $value)
    {

        $str_Class = 'App\Models\\'.ucfirst( $model );

        if (!class_exists($str_Class))
            trigger_error("#ValidateTrait->getUniqueDB(), nome da classe <b>[{$str_Class}]</b> não existe no sistema - ", E_USER_ERROR);


        $model = Container::getServices($str_Class);

        $res = $model
        	->where("$fields", $value, 'LIKE')
        	->all()
        	->getResult();

        // pp($res,1);
        return $res;

    }

}