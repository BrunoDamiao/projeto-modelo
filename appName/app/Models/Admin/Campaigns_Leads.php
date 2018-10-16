<?php
namespace App\Models\Admin;

use FwBD\Model\BaseModel;

class Campaigns_Leads extends BaseModel
{
    protected $table = 'tb_campaigns_leads';
    protected $preFix = 'leads_';
    // protected $preFix = 'cpld_';

    # Fillable
    protected $fillable = [
        'campaigns_id', 'leads_id', 'cpld_created', 'cpld_updated'
    ];

    # Filters/Rules
    protected $rules = [
        'campaigns_id' => 'requerid',
        'leads_id'     => 'requerid',
    ];

    ## herdam os metodos da class modelpdo

}