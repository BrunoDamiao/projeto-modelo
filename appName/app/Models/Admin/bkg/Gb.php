<?php
namespace App\Models\Admin;

use FwBD\Model\BaseModel;

class Gb extends BaseModel
{
    protected $table = 'FwBD.tb_gb';
    protected $preFix = 'gb_';

    # Fillable
    protected $fillable = [
        'post_id', 'gb_title', 'gb_description', 'gb_uri', 'gb_thumb', 'gb_created', 'gb_modified', 'gb_auth'    
    ];

    # Filters/Rules
    protected $rules = [
        'post_id' => 'requerid' ,
        'gb_thumb' => 'required-file | types-file:image/png,image/jpeg,image/jpg | max-file-size:2',
    ];

    ## herdam os metodos da class modelpdo

}
# 'post_id', 'gb_title', 'gb_description', 'gb_uri', 'gb_thumb', 'gb_created', 'gb_modified', 'gb_auth'

