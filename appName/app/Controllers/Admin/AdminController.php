<?php
namespace App\Controllers\Admin;

use FwBD\Controller\BaseController;
use FwBD\DI\Container;

class AdminController extends BaseController
{

    private $model, $image;
    // private $dbConfig;
    private $root  = '/admin/profile';

    public function __construct($params)
    {
        parent::__construct($params);
        Container::setFilter(['auth']);
        Container::setTemplateView('admin.templates.template');
        $this->model = Container::getServices('App\Models\Admin\User');

        $this->image = Container::getServices('FwBD\BrImage\Image');
        $this->image->setDirModel('user');
        $this->image->setflagMerge(0);
    }

    /**
     * Methods GET
     */

        public function getIndex()
        {
            $title = "Dashboard";

            $dbName = (self::getJson()['drive'] === 'sqlite')? '':
            self::getJson()['name'].'.';

            $user = $this->model
                ->setTable('tb_user AS U')
                ->select('U.user_id, U.level_id, U.user_name, U.user_email, U.user_password, U.user_show, U.user_thumb, U.user_obs, U.user_uri, U.user_created, U.user_updated, U.user_status, U.user_author, (SELECT A.user_name FROM '.$dbName.'tb_user AS A
                    WHERE A.user_id = U.user_author) AS name_author,
                    L.level_id, L.level_category, L.level_name ')
                ->join('tb_level AS L','L.level_id = U.level_id' )
                ->where('U.user_id', 1, '!=')
                // ->where('U.user_id', $authSession->session_user, '!=')
                ->all()
                ->getResult();

            // pp($user,1);
            Container::getView('admin.home', compact('title','user','post','json'));
        }

        public function getSettings()
        {
            $title = "Manager Settings";

            $dataBase = self::getJson();

            $data = [
                'init' => [
                    'debug'     => APP_DEBUG,
                    'key'       => $dataBase['proj_key'],
                    'root'      => APP_ROOT,
                ],
                'app' => [
                    'app'       => $dataBase['proj_category'],
                    'title'     => $dataBase['proj_title'],
                    'slogan'    => $dataBase['proj_slogan'],
                    'name'      => $dataBase['user_name'],
                    'email'     => $dataBase['user_email'],
                    'paginator' => $dataBase['proj_paginator'],
                ],
                'database' => [
                    'drive'     => $dataBase['drive'],
                    'host'      => $dataBase['host'],
                    'dbname'    => $dataBase['name'],
                ],
                'path_system' => [
                    'root'      => PATH_ROOT,
                    'views'     => PATH_VIEWS,
                    'storage'   => PATH_STORAGE,
                    'database'  => PATH_DATABASE,
                ],
                'path_midias' => [
                    'midias'    => PATH_MIDIAS,
                    'favicon'   => PATH_FAVICON,
                    'logo'      => PATH_LOGO,
                    'logo2'     => PATH_LOGO2,
                    'mescle'    => PATH_MESCLE,
                    'loader'    => PATH_LOADER,
                    'avatar'    => PATH_AVATAR,
                ],
            ];

            Container::getView('admin.settings', compact('title','data'));
        }

        public function getProfile()
        {
            $title = "Manager Profile";

            $dbName = (self::getJson()['drive'] === 'sqlite')? '':
            self::getJson()['name'].'.';

            $data = $this->model
                ->setTable('tb_user AS U')
                ->select('U.user_id, U.level_id, U.user_name, U.user_email, U.user_password, U.user_show, U.user_thumb, U.user_obs, U.user_uri, U.user_created, U.user_updated, U.user_status, U.user_author, (SELECT A.user_name FROM '.$dbName.'tb_user AS A
                    WHERE A.user_id = U.user_author) AS name_author,
                    L.level_id, L.level_category, L.level_name ')
                ->join('tb_level AS L','L.level_id = U.level_id' )
                ->where('U.user_id', $this->getParams('id'))
                ->all()
                ->getResult()[0];

            $level = Container::getServices('App\Models\Admin\Level')
                ->where('level_category', 'MASTERKEY', '!=')
                ->where('level_name', '--', '!=' )
                ->all()
                ->getResult();

            Container::getView('admin.profile', compact('title','data','level'));
        }

        public function getSearch()
        {
            $title  = "Search";
            $search = ($this->params[0] != 'all')? $this->params[0] : '';
            #$datax   = $this->createFakeSearch(6);

            $json = self::getJson();
            if ( $json['drive'] === 'sqlite' )
                $sqlP = ['sqlite_master', 'name as tab', 'name', 'sqlite_%', 'NOT LIKE'];
            else
                $sqlP = ['information_schema.tables', 'table_name as tab', 'table_schema ',$json['name'], '='];

            $tables = $this->model
                ->setTable($sqlP[0])
                ->select($sqlP[1])
                ->where($sqlP[2], $sqlP[3], $sqlP[4])
                ->all()
                ->getResult();
            // pp($tables,1);

            $db = array();
            foreach ($tables as $k => $v) {
                $md = ucfirst(str_replace('tb_', '', $v->tab));
                $f  = str_replace('tb_', '', $v->tab).'_';

                $db[$md] = Container::getServices('App\Models\Admin\\'.$md)
                ->setTable($v->tab)
                ->where("{$f}id", '1', '!=')
                ->where("{$f}id", '2', '!=')
                ->where("{$f}name", "%{$search}%", 'LIKE')
                ->where("{$f}obs", "%{$search}%", 'LIKE')
                // ->orWhere("{$f}obs", "%{$search}%", 'LIKE')
                ->all()
                ->getResult();
            }
            // pp($db,1);
            $data = $this->createDataSearch($db);
            // pp($data,1);

            Container::getView('admin.search', compact('title','data','search'));
        }


    /**
     * Methods POST
     */

        public function postSearch()
        {
            $request = Container::getServices('FwBD\Request\Request');
            $data    = cleanString($request->post()['search']);
            $search  = !empty($data)? $data:'all';
            redirect('/admin/search/'.$search);
        }

        public function postProfile()
        {
            # PARAMS
            $page = $this->getParams();
            $id   = $this->getParams('id');
            $url  = $id;

            #REQUEST
            $request = Container::getServices('FwBD\Request\Request');
            $dataformEdit = $request->all();

            #VALIDATE
            $validate = Container::getServices('FwBD\Validate\Validate','Admin/User');
            $this->model->setRules([
                'user_name' => 'requerid | min:3 | max:20',
                'user_email' => 'requerid | email | min:2 | max:15',
                'user_password' => 'min:4 | max:12',
            ] );
            $validate->validateData($this->model->getRules(), $dataformEdit);

            if ($validate->getStatus()) {
                setDataInput($dataformEdit);
                setMsgFlash('warning', $validate->getMessages());
                return redirect("{$this->root}/{$url}");
            }

            # MODEL
            $user = $this->model->find( $id );

            #IMAGE
            if ( $request->hasfiles('user_thumb') ) {
                $this->image->setFileImage('user_thumb');
                $this->image->setNameImage($dataformEdit['user_name']);
                $this->image->moveImage();
                $this->image->deleteImage($user->user_thumb);

                if ( $this->image->getMsgError() ) {
                    setDataInput($dataformEdit);
                    setMsgFlash('danger', $this->image->getMsgError() );
                    redirect("{$this->root}/{$url}");
                }

                $dataformEdit['user_thumb'] = $this->image->getNameImage();
            }

            # Processador Password Encrypt
            $encryptForm = \FwBD\Encrypt\Encrypt::hashCode($dataformEdit['user_password']);
            if ( $user->user_password === $encryptForm ) {
                unset($dataformEdit['user_password']);
                unset($dataformEdit['user_show']);
            }else{
                $dataformEdit['user_show'] = $dataformEdit['user_password'];
                $dataformEdit['user_password'] = $encryptForm;
            }

            $dataformEdit += [
                'user_uri'      => cleanString($dataformEdit['user_name']),
                'user_updated'  => date("Y-m-d H:i:s"),
            ];

            # MODEL
            if ($this->model->update($user->user_id, $dataformEdit) ){
                $thumb = $_SESSION['Auth']['session_user_thumb'];
                if ( empty($thumb) || $thumb !== $dataformEdit['user_thumb'] )
                    $_SESSION['Auth']['session_user_thumb'] = $dataformEdit['user_thumb'];

                setMsgFlash('success', "O registro <strong>{$dataformEdit['user_name']}</strong>, foi editado com sucesso!");
                redirect("{$this->root}/{$url}");
            }else{
                setMsgFlash('danger', "Error ao editar registro! Favor entrar em contato com suporte.");
                redirect("{$this->root}/{$url}");
            }
        }


    /**
     * Methods HELPERS
     */

        private function getParams($tipo='')
        {
            if ( isset($this->params[1]) ) {
                $page = $this->params[0];
                $id = $this->params[1];
            }else{
                // $page = '1';
                $page = 1;
                $id = $this->params[0];
            }

            if ( empty($tipo) )
                return $page;
            else
                return $id;
        }

        private function createDataSearch($data2)
        {
            $rs = array();
            $dt = array();
            $i = 0;
            foreach ($data2 as $md => $data) {
                foreach ($data as $k => $v) {
                    $i ++; $dt[$i] = $v;
                }
                foreach ($dt as $k => $v) {
                    foreach ($v as $kk => $vv) {
                        $f  = explode('_', $kk);
                        $md = $f[0];
                        $fie = str_replace($f[0], 'search', $kk);
                        $dtx['search_model']= $md;
                        $dtx[$fie]= $vv;
                        /*if ( $fie == 'search_name' ) {
                            $i = str_replace($f[1], 'title', $fie);
                            $dtx[$i] = $vv;
                        }elseif ( $fie == 'search_obs' ) {
                            $i = str_replace($f[1], 'msg', $fie);
                            $dtx[$i] = $vv;
                        }else
                            $dtx[$fie]= $vv;*/
                    }
                    $rs[$k]= (object) $dtx;
                }
            }
            return $rs;
        }

        private function createFakeSearch($num=4)
        {
            $rs = array();
            for ($i=1; $i < $num; $i++) {
                $rs[$i] = (object) [
                    'title' => 'Card Title '.$i,
                    'msg' => 'Hey there, I am a very simple card. I am good at containing small bits of information.'
                ];
            }
            return $rs;
        }






}