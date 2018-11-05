<?php
namespace App\Models\Admin;

use FwBD\Model\BaseModel;

class Level extends BaseModel
{
    protected $table = 'tb_level';
    protected $preFix = 'level_';

    # Fillable
    protected $fillable = [
        'level_category', 'level_name', 'level_obs', 'level_uri', 'level_created', 'level_updated', 'level_status', 'level_author'
    ];

    # Filters/Rules
    protected $rules = [
        'level_category' => 'requerid | unique:level | min:1 | max:20',
        'level_name'     => 'requerid | unique:level | min:3 | max:20',
        /*'level_category' => 'requerid | min:3 | max:20',
        'level_name'     => 'requerid | min:3 | max:20',*/
    ];

    ## herdam os metodos da class modelpdo

}