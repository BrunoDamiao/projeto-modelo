<?php
namespace App\Models\Admin;

use FwBD\Model\BaseModel;
// use FwBD\BrUp\Upload;

class Post extends BaseModel
{
    protected $table = 'FwBD.tb_post';
    protected $preFix = 'post_';

    # Fillable
    protected $fillable = [
        'post_category','post_title', 'post_description', 'post_uri', 'post_thumb', 'post_created', 'post_modified', 'post_status', 'post_author'
    ];

    # Filters/Rules
    protected $rules = [
        // 'post_category'   => 'requerid',
        'post_title'      => 'requerid | unique:post | min:2 | max:50',
        'post_description'=> 'max:65535',
        'post_thumb'      => 'required-file | types-file:image/png,image/jpeg,image/jpg | max-file-size:2',
    ];

    ## herdam os metodos da class modelpdo
    
}

# 'post_title', 'post_description', 'post_uri', 'post_thumb', 'post_created',         'post_modified', 'post_status', 'post_author'