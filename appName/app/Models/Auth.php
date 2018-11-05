<?php
namespace App\Models;

use FwBD\Model\BaseModel;

class Auth extends BaseModel
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
        'user_email' => 'requerid | email | min:2 | max:15',
        'user_password' => 'requerid | min:4 | max:8'
    ];

    ## herdam os metodos da class modelpdo

}

# 'user_nome', 'user_email', 'user_senha', 'user_foto','user_obs', 'user_status', 'user_auth'
