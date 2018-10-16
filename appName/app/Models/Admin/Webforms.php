<?php
namespace App\Models\Admin;

use FwBD\Model\BaseModel;

class Webforms extends BaseModel
{
    protected $table = 'tb_forms';
    protected $preFix = 'forms_';

    # Fillable
    protected $fillable = [
        'campaigns', 'forms_name', 'forms_code', 'forms_obs', 'forms_uri', 'forms_created', 'forms_updated', 'forms_status', 'forms_author'
    ];

    # Filters/Rules
    protected $rules = [
        'campaigns'  => 'requerid',
        'forms_name' => 'requerid | unique:level | min:3 | max:20',
        'forms_code' => 'requerid | min:5',
    ];

    ## herdam os metodos da class modelpdo

}