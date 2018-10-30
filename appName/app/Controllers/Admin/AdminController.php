<?php
namespace App\Controllers\Admin;

use FwBD\Controller\BaseController;
use FwBD\DI\Container;

class AdminController extends BaseController
{
    public function __construct($params)
    {
        parent::__construct($params);
        Container::setFilter(['auth']);
        Container::setTemplateView('admin.templates.template');
        $this->model = Container::getServices('App\Models\Admin\User');
    }

    /**
     * Methods GET
     */

    public function getIndex()
    {
        $title = 'Dashboard';
        Container::getView('admin.home', compact('title','user','post','json'));
    }


    /**
     * Methods POST
     */


    /**
     * Methods HELPERS
     */








}