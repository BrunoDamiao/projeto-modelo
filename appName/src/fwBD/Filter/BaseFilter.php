<?php
namespace FwBD\Filter;

abstract class BaseFilter
{

    abstract public function Filter($data='');

    public static function getFilter(array $name)
    {
        foreach ($name as $name => $params) {

            if ( is_int($name) ) {
                $filterName   = $params;
                $filterParams = '';
            }else{
                $filterName   = $name;
                $filterParams = $params;
            }

            switch (ucwords($filterName)) {
                case 'Auth':
                    $str_Path = 'FwBD\\Filter\\SystemFilter';
                    $str_Method  = 'Auth';
                break;

                case 'SetupIn': # criado p/ class DBConect()
                    $str_Path = 'FwBD\\Filter\\SystemFilter';
                    $str_Method  = 'Filter';
                break;

                case 'SetupOut': # criado p/ class DBConect()
                    $str_Path = 'FwBD\\Filter\\SystemFilter';
                    $str_Method  = 'SetupOut';
                break;

                default:
                    $str_Path = 'App\Filters\\' . FILTERS[$filterName];
                    $str_Method = 'Filter';
                break;
            }

            if (class_exists($str_Path)) {
                $ObjFilter = new $str_Path;

                if (method_exists($ObjFilter, $str_Method)) {
                    call_user_func_array([$ObjFilter, $str_Method], [$filterParams]);
                }

            }else{
            trigger_error("#BaseFilter::getFilter(), a classe <b>[{$str_Class}]</b> não existe ou não foi registrado no arquivo de configurações: <i>App\Filters.php</i> - ", E_USER_ERROR);
        }

        }

    }







    public static function getFilterXXXX(array $name, $params='')
    {

        pp($name);
        pp($params);

       /* if ( is_int(var)) {
            # code...
        }*/

        foreach ($name as $k => $nameFiltro) :

            switch ($nameFiltro) {
                case 'auth':
                    $str_Path = 'FwBD\\Filter\\AuthFilter'; break;

                case 'createDB': # inválido
                    $str_Path = 'FwBD\\Filter\\CreateDbFilter'; break;

                case 'SetupSystem':
                    $str_Path = 'FwBD\\Filter\\SetupSystemFilter'; break;

                default:
                    $str_Path = 'App\Filters\\' . FILTERS[$nameFiltro]; break;
            }

        endforeach;
        // pp($str_Path);

        $str_Path = explode('@', $str_Path);
        $str_Class = $str_Path[0];
        $str_Method = (!empty($str_Path[1]))? $str_Path[1] : 'Filter' ;
        $params = (!empty($params))? $params : '' ;

        if (class_exists($str_Class)) {
            $ObjFilter = new $str_Class;

            if (method_exists($ObjFilter, $str_Method)) {
                call_user_func_array([$ObjFilter, $str_Method], [$params]);
            }

        }else{
            trigger_error("#Container->getFilter(), a classe <b>[{$str_Class}]</b> não existe ou não foi registrado no arquivo de configurações: <i>App\Filters.php</i> - ", E_USER_ERROR);
        }

    }


}