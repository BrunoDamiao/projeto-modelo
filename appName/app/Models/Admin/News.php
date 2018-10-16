<?php
namespace App\Models\Admin;

use FwBD\Model\BaseModel;

class News extends BaseModel
{
    protected $table = 'tb_news';
    protected $preFix = 'news_';

    # Fillable
    protected $fillable = [
        'campaigns', 'news_name', 'news_email', 'news_uri', 'news_created', 'news_updated', 'news_status', 'news_author'
    ];

    # Filters/Rules
    protected $rules = [
        'campaigns'  => 'requerid | min:3 | max:20',
        'news_name'  => 'requerid | unique:level | min:3 | max:20',
        'news_email' => 'requerid | email | min:3 | max:20',
    ];

    ## herdam os metodos da class modelpdo

}