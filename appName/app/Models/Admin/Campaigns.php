<?php
namespace App\Models\Admin;

use FwBD\Model\BaseModel;

class Campaigns extends BaseModel
{
    protected $table  = 'tb_campaigns';
    protected $preFix = 'campaigns_';

    # Fillable
    protected $fillable = [
        'campaigns_title', 'campaigns_obs','campaigns_created', 'campaigns_updated', 'campaigns_status', 'campaigns_author', 
    ];

    # Filters/Rules
    protected $rules = [
        'campaigns_title' => 'requerid | unique:campaigns | min:3 | max:255',
    ];

    ## herdam os metodos da class modelpdo

}