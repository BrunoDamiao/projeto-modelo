<?php
namespace App\Controllers\Admin;

use FwBD\Controller\BaseController;
use FwBD\DI\Container;

class UserController extends BaseController
{
    private $model;
    private $root   = '/admin/user';
    private $totalPage = APP_PAGINATOR;


    public function __construct($params)
    {
        parent::__construct($params);
        Container::setFilter(['Auth']);
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
        $title = 'Manager Users';

        $authSession = (object) Container::getSession('get', ['Auth']);

        $data = $this->model
            ->setTable('tb_user AS U')
            ->select('U.user_id, U.level_id, U.user_name, U.user_email, U.user_password, U.user_show, U.user_thumb, U.user_obs, U.user_uri, U.user_created, U.user_updated, U.user_status, U.user_author, (SELECT A.user_name FROM appModelo.tb_user AS A
                WHERE A.user_id = U.user_author) AS name_author,
                L.level_id, L.level_category, L.level_name ')
            ->join('tb_level AS L','L.level_id = U.level_id' )
            ->where('U.user_id', 1, '!=')
            ->where('U.user_id', $authSession->session_user, '!=')
            ->all()
            ->getResult();
            // pp($data->getResult(),1);
            // pp($data,1);

        Container::getView('admin.user.user', compact('title','data'));
    }

    public function getStatus()
    {
        #GET DATA DB
        $user = $this->model->find( $this->params[0] );
        $dataformEdit['user_status'] = ($user->user_status == 1)? 0 : 1;

        # MODEL
        if ($this->model->update($user->user_id, $dataformEdit) ){
            setMsgFlash('success', "<strong>Parabéns!</strong> O registro: <strong>{$user->user_name}</strong>, foi editado com sucesso!");
            redirect("{$this->root}");
        }else{
            setMsgFlash('danger', "<strong>Atenção!</strong> Error ao editar status do registro! Favor entrar em contato com suporte.");
            redirect("{$this->root}");
        }
    }

    public function getJstatus()
    {
        # REQUEST
        $data = Container::getServices('FwBD\Request\Request')->get();

        # MODEL
        $user = $this->model->find($data['id']);
        $dataStatus['user_status'] = ($user->user_status == 1)? 0 : 1;

        # UPDATE
        if ($this->model->update($user->user_id, $dataStatus) ){

            if ( $user->user_status == 1 ) {
                $alertClas = 'alert-warning';
                $alertStr  = 'desativado';
            }else{
                $alertClas = 'alert-success';
                $alertStr  = 'ativado';
            }

            echo json_encode([
                'msg_alert' => $alertClas,
                'msg_text'  => 'O registro ' . $user->user_name.', foi <strong>'.$alertStr.'</strong> com sucesso!',
            ],false);

        }else{

            echo json_encode([
                'msg_alert' => 'alert-success',
                'msg_text'  => "Error ao mudar o status do registro! Favor entrar em contato com suporte.",
            ],false);

        }
    }

    public function getCreate()
    {
        $title = 'Manager Users';
        $data = [];
        $authSession = (object) Container::getSession('get', ['Auth']);

        $level = Container::getServices('App\Models\Admin\Level')
            ->select('level_id, level_category,
                level_name')
            ->where('level_category', 'MASTERKEY','!=')
            ->where('level_name', '--', '!=')
            ->all()
            ->getResult();

        $category = Container::getServices('App\Models\Admin\Level')
            ->select('level_id, level_category,
                level_name')
            ->where('level_category', 'MASTERKEY','!=')
            ->where('level_name', '--')
            ->all()
            ->getResult();

        Container::getView('admin.user.user-new-edit', compact('title','data','level','category'));
    }

    public function getProfile()
    {
        $title = 'Manager Profile';
        // $data  = $this->model->find( $this->getParams('id') );
        $data = $this->model
            ->setTable('tb_user AS U')
            ->select('U.user_id, U.level_id, U.user_name, U.user_email, U.user_password, U.user_show, U.user_thumb, U.user_obs, U.user_uri, U.user_created, U.user_updated, U.user_status, U.user_author, (SELECT A.user_name FROM appBruno.tb_user AS A
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
        $category = Container::getServices('App\Models\Admin\Level')
            ->where('level_category', 'MASTERKEY', '!=')
            ->where('level_name', '--' )
            ->all()
            ->getResult();

        pp($data,1);

        Container::getView('admin.user.user-profile', compact('title','data','level','category'));
    }

    public function getEdit()
    {
        $title = 'Manager Users';
        // $data  = $this->model->find( $this->getParams('id') );
        $data = $this->model
            ->setTable('tb_user AS U')
            ->select('U.user_id, U.level_id, U.user_name, U.user_email, U.user_password, U.user_show, U.user_thumb, U.user_obs, U.user_uri, U.user_created, U.user_updated, U.user_status, U.user_author, (SELECT A.user_name FROM appBruno.tb_user AS A
                WHERE A.user_id = U.user_author) AS name_author,
                L.level_id, L.level_category, L.level_name ')
            ->join('tb_level AS L','L.level_id = U.level_id' )
            ->where('U.user_id', $this->getParams('id'))
            ->all()
            ->getResult()[0];
        $level = Container::getServices('App\Models\Admin\Level')
            ->where('level_name', '--','!=')
            ->where('level_name', 'MASTERKEY','!=')
            ->all()
            ->getResult();

        Container::getView('admin.user.user-new-edit', compact('title','data','level'));
    }

    public function getDelete()
    {
        # PARAMS
        $page = $this->getParams();
        $id   = $this->getParams('id');

        # MODEL
        $data = $this->model->find($id);
        // pp($data,1);

        $StrDelete  = "Atenção! Deseja excluir o registro <strong>{$data->user_name}</strong> permanentemente? ";

        $StrDelete .= "<a href='/admin/user/destroy/{$data->user_id}' class='btn btn-success btn-xs' title='Sim'>Sim<i class='glyphicon glyphicon-ok-sign'> </i></a> ";
        $StrDelete .= " | ";
        $StrDelete .= " <a href='/admin/user' class='btn btn-danger btn-xs' title='Não' data-dismiss='alert' aria-hidden='true'>Não<i class='glyphicon glyphicon-remove-sign'></i></a>";

        setMsgFlash('warning', "$StrDelete");

        return redirect("{$this->root}");
    }

    public function getDestroy()
    {
        # MODEL
        $page = $this->getParams();
        $id   = $this->getParams('id');
        $data = $this->model->find($id);

        #IMAGE
        if ( !$this->image->deleteImage($data->user_thumb) ){
            setMsgFlash('danger', $this->image->getMsgError());
        }

        # MODEL
        if ( $data->user_id == Container::getSession('get', ['Auth'])['session_user'] ){
            setMsgFlash('warning', "Usuário <strong>{$data->user_name}</strong> não pode ser exluido, pois se encontra logado no sistema!");
            redirect("{$this->root}");
        }elseif ( !empty($data->user_id) && $this->model->delete($data->user_id) ){
            setMsgFlash('success', "Registro <strong>{$data->user_name}</strong> foi removido com sucesso!");
            redirect("{$this->root}");
        }else{
            setMsgFlash('danger', "Error ao excluir o registro <strong>{$data->user_name}</strong>! Tente novamente mais tarde.");
            redirect("{$this->root}");
        }
    }




    /**
     * Methods POST
     */
    public function postCreate()
    {
        #REQUEST
        $request = Container::getServices('FwBD\Request\Request');
        $dataStore = $request->all();
        // array_pop($dataStore);

        #VALIDATE
        $validate = Container::getServices('FwBD\Validate\Validate','Admin\User');
        $validate->validateData($this->model->getRules(), $dataStore);

        if ($validate->getStatus()) {
            setDataInput($dataStore);
            setMsgFlash('warning', $validate->getMessages());
            return redirect("{$this->root}/create");
        }
        // pp($dataStore,1);

        #IMAGE
        if ( $request->hasfiles('user_thumb') ) {

            $this->image->setFileImage('user_thumb');
            $this->image->setNameImage($dataStore['user_name']);
            $this->image->moveImage();

            if ( $this->image->getMsgError() ) {
                setDataInput($dataStore);
                setMsgFlash('danger', $this->image->getMsgError() );
                return redirect("{$this->root}/create");
            }

            $dataStore['user_thumb'] = $this->image->getNameImage();
        }

        #HASH SENHA
        $dataStore['user_show'] = $dataStore['user_password'];
        $getPassword = \FwBD\Encrypt\Encrypt::hashCode($dataStore['user_password']);
        $dataStore['user_password'] = $getPassword;
        $dataStore += $this->setDataDefault($dataStore);
        // pp($dataStore,1);

        # MODEL
        if ($this->model->insert($dataStore) ){
            setMsgFlash('success', "O registro <strong>{$dataStore['user_name']}</strong>, foi criado com sucesso!");
            redirect("{$this->root}/create");
        }else{
            setMsgFlash('danger', "Error ao criar novo registro! Favor entrar em contato com suporte.");
            redirect("{$this->root}/create");
        }
    }


    public function postEdit()
    {
        # PARAMS
        $page = $this->getParams();
        $id   = $this->getParams('id');
        $url  = $id;

        #REQUEST
        $request = Container::getServices('FwBD\Request\Request');
        $dataformEdit = $request->all();
        array_pop($dataformEdit);

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
            return redirect("{$this->root}/edit/{$url}");
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
                redirect("{$this->root}/edit/{$url}");
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

        $dataformEdit += $this->setDataDefault($dataformEdit,'edit');
        // pp($dataformEdit,1);

        # MODEL
        if ($this->model->update($user->user_id, $dataformEdit) ){
            setMsgFlash('success', "O registro: <strong>{$dataformEdit['user_name']}</strong>, foi editado com sucesso!");
            redirect("{$this->root}/edit/{$url}");
        }else{
            setMsgFlash('danger', "Error ao editar registro! Favor entrar em contato com suporte.");
            redirect("{$this->root}/edit/{$url}");
        }
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
        array_pop($dataformEdit);

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
            return redirect("{$this->root}/profile/{$url}");
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
                redirect("{$this->root}/profile/{$url}");
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

        $dataformEdit += $this->setDataDefault($dataformEdit,'edit');

        # MODEL
        if ($this->model->update($user->user_id, $dataformEdit) ){
            setMsgFlash('success', "O registro <strong>{$dataformEdit['user_name']}</strong>, foi editado com sucesso!");
            redirect("{$this->root}/profile/{$url}");
        }else{
            setMsgFlash('danger', "Error ao editar registro! Favor entrar em contato com suporte.");
            redirect("{$this->root}/profile/{$url}");
        }
    }




    /**
     * Methods MODALS
     */




    /**
     * Methods HELPERS
     */

    private function createDataForm()
    {
        $request = Container::getServices('FwBD\Request\Request');
        $form = $request->all();
        array_pop($form);

        if ( empty($form['user_Id']) )
            unset($form['user_Id']);

        if ( count($form) > 0 )
            $dataForm = $form;
        else {
            if ( isset($dataForm['user_Id']) ) {
                $dataForm['user_Id'] = $this->params[1];
                $dataForm['user_Pesquisar'] = $this->params[2];
            }else
                $dataForm['user_Pesquisar'] = $this->params[1];

        }

        return $dataForm;
    }


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


    private function setDataDefault(array $data, $type='')
    {
        $authSession = (object) Container::getSession('get', ['Auth']);

        if ( empty($type) ) {
            $result = [
                'user_uri'      => cleanString($data['user_name']),
                'user_created'  => date("Y-m-d H:i:s"),
                'user_updated'  => date("Y-m-d H:i:s"),
                'user_status'   => '1',
                'user_author'   => $authSession->session_user
            ];
        }else{
            $result = [
                'user_uri'      => cleanString($data['user_name']),
                'user_updated'  => date("Y-m-d H:i:s"),
                'user_author'   => $authSession->session_user
            ];
        }

        return $result;
    }

    private function setMgsJson($alert='success', $text)
    {
        echo json_encode([
                'msg_alert' => "$alert",
                'msg_text'  => "$text"
            ],false);
    }


}