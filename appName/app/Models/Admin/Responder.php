<?php
namespace App\Models\Admin;

use FwBD\Model\BaseModel;

class Responder extends BaseModel
{
    protected $table = 'tb_leads';
    protected $preFix = 'leads_';

    # Fillable
    protected $fillable = [
        'leads_name', 'leads_email', 'leads_uri', 'leads_created', 'leads_updated', 'leads_status', 'leads_author'
    ];

    # Filters/Rules
    protected $rules = [
        'leads_name'  => 'requerid | min:3 | max:20',
        'leads_email' => 'requerid | email | unique:level | min:3 | max:20',
    ];

    ## herdam os metodos da class modelpdo

}