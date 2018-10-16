<?php
namespace App\Controllers;

use FwBD\Controller\BaseController;
use FwBD\DI\Container;
use FwBD\DBConect\DBConect;
use FwBD\Encrypt\Encrypt;
use FwBD\Mail\Email;
use FwBD\Json\Json;

class SetupController extends BaseController
{
    private $model;


    public function __construct($params)
    {
        parent::__construct($params);
        # Create dataBase com tb_User
        // Container::setFilter(['createDB']);
        // Container::setFilter(['SetupSystem']);
        // Container::setTemplateView('setup.templates.template');
        // $this->model = Container::getServices('App\Models\Auth');
    }


    /**
     * Methods GET
     */

    public function getIndex()
    {
        $title = 'Settings DB';
        $data = [
            'setup_host'        => 'localhost',
            // 'setup_host'        => '',
            'setup_dbname'      => 'appModelo',//DBS,
            'setup_usname'      => 'root',
            'setup_password'    => 'beca',
            'setup_charset'     => 'utf8',
            'setup_collation'   => 'utf8_unicode_ci'
        ];
        Container::getView('setup.settings', compact('title','data'));
    }



    /**
     * Methods POST
     */
        public function postCreate()
        {

            $request = new \FwBD\Request\Request;
            $rs = $request->post();

            $validate = new \FwBD\Validate\Validate;
            $rules = [
                'setup_host'     => 'requerid | min:2 | max:255',
                'setup_dbname'   => 'requerid | min:2 | max:255',
                'setup_usname'   => 'requerid | min:2 | max:255',
                'setup_password' => 'requerid | min:2 | max:255',
                'setup_charset'  => 'requerid | min:2 | max:255',
                'setup_collation'=> 'requerid | min:2 | max:255'
            ];
            $validate->validateData($rules, $rs);

            if ($validate->getStatus()) {
                setDataInput($rs);
                setMsgFlash('warning', $validate->getMessages());
                return redirect("/setup");
            }

            # CREATE JSON DB
            pp($rs,1);

            $path = DIRECTORY_SQLITE.'configBD.json';
            $dt = FwBD\Json\Json::create($rs, $path);
            pp($dt);

        }



    /**
     * Methods HELPERS
     */





}