<?php
namespace App\Models\Admin;

use FwBD\Model\BaseModel;

class User extends BaseModel
{
    protected $table = 'tb_user';
    protected $preFix = 'user_';

    # Fillable
    protected $fillable = [
        'level_id', 'user_name', 'user_email', 'user_password', 'user_show', 'user_thumb', 
        'user_obs', 'user_uri', 'user_created', 'user_updated', 'user_status','user_author' 
    ];

    # Filters/Rules
    protected $rules = [
        // 'level_id'      => 'requerid',
        'user_name'     => 'requerid | unique:user | min:3 | max:20',
        'user_email'    => 'requerid | email | min:2 | max:15',
        'user_password' => 'requerid | min:4 | max:12',
        'user_thumb'    => 'required-file | types-file:image/png,image/jpeg,image/jpg | max-file-size:2',
    ];

    ## herdam os metodos da class modelpdo

}