<?php
namespace App\Models\Admin;

use FwBD\Model\BaseModel;

class Product extends BaseModel
{
    protected $table  = 'tb_product';
    protected $preFix = 'product_';

    # Fillable
    protected $fillable = [
        'product_category','product_title', 'product_description', 'product_prince', 
        'product_uri', 'product_thumb', 'product_obs', 'product_created', 
        'product_modified', 'product_status', 'product_auth'
    ];

    # Filters/Rules
    protected $rules = [
        'product_category'   => 'requerid',
        'product_title'      => 'requerid | unique:post | min:2 | max:50',
        'product_description'=> 'max:65535',
        'product_thumb'      => 'required-file | types-file:image/png,image/jpeg,image/jpg | max-file-size:2',
    ];

    ## herdam os metodos da class modelpdo
    
}
