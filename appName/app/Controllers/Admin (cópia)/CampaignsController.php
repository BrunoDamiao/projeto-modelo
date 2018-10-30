<?php
namespace App\Controllers\Admin;

use FwBD\Controller\BaseController;
use FwBD\DI\Container;

class CampaignsController extends BaseController
{
    
    private $model;
    private $baseUri   = '/admin/campaigns';
    private $totalPage = APP_PAGINATOR; 


    public function __construct($params)
    {
        parent::__construct($params);
        Container::setFilter(['auth']);
        Container::setTemplateView('admin.templates.template');
        $this->model = Container::getServices('App\Models\Admin\Campaigns');
        // $this->modelForms = Container::getServices('App\Models\Admin\Forms');
    }

    /**
     * Methods GET
    */

        public function getIndex()
        {
            Container::setFilter(['userGestao']);

            $title = 'Manager Campaigns';

            # set global sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';

            $data = $this->model
                ->setTable('tb_campaigns AS C')
                ->select('C.campaigns_id, C.campaigns_title, C.campaigns_obs, C.campaigns_created, C.campaigns_updated, C.campaigns_status, C.campaigns_author,
                    U.user_name')
                ->join('tb_user AS U', 'C.campaigns_author = U.user_id')
                ->all();

            /*$data = $this->model
                ->setTable('tb_campaigns AS C')
                ->select('C.campaigns_id, C.campaigns_title, C.campaigns_obs, C.campaigns_created, C.campaigns_updated, C.campaigns_status, C.campaigns_author,
                    U.user_name, if(forms_id is not null, "Views", "Create") forms')
                ->join('tb_user AS U', 'C.campaigns_author = U.user_id')
                ->join('tb_forms AS F', 'C.campaigns_id = F.campaigns', 'LEFT OUTER JOIN')
                ->groupBy('F.campaigns')
                ->orderBy('C.campaigns_id', 'ASC')
                ->all();*/

            Container::getView('admin.campaigns.campaigns', compact('title','data'));
        }

        public function getJstatus()
        {
            # REQUEST
            $request = Container::getServices('FwBD\Request\Request');
            $data = $request->get();

            # MODEL
            $campaigns = $this->model->find( $data['id'] );
            $dataStatus['campaigns_status'] = ($campaigns->campaigns_status == 1)? 0 : 1;

            # UPDATE
            if ($this->model->update($campaigns->campaigns_id, $dataStatus) ){

                if ( $campaigns->campaigns_status == 1 ) {
                    $alertClas = 'alert-warning';
                    $alertStr  = 'desativado';
                }else{
                    $alertClas = 'alert-success';
                    $alertStr  = 'ativado';
                }

                echo json_encode([
                    'msg_alert' => $alertClas, 
                    'msg_text'  => 'O registro ' . $campaigns->campaigns_title.', foi <strong>'.$alertStr.'</strong> com sucesso!', 
                ],false);

            }else{

                echo json_encode(['msg_alert' => 'alert-danger', 
                    'msg_text'  => 'Error ao mudar o status do registro! Favor entrar em contato com suporte.', 
                ],false);

            }
        }

        public function getCreate()
        {
            Container::setFilter(['userGestao']);

            $title = 'Manager Campaigns';
            $data  = [];

            Container::getView('admin.campaigns.campaigns-new-edit', compact('title','data'));
        }

        public function getEdit()
        {
            Container::setFilter(['userGestao']);

            $title = 'Manager Campaigns';
            $data = $this->model->find( $this->params[0] );
            Container::getView('admin.campaigns.campaigns-new-edit', compact('title','data'));
            // Container::getView('admin.categorySystem.categorySystem-new-edit', compact('title','data'));
        }

        public function getDelete()
        {
            # MODEL
            $data = $this->model->find( $this->params[0] );

            $StrDelete  = "Atenção! Deseja excluir o registro <strong>{$data->campaigns_title}</strong> permanentemente? ";

            $StrDelete .= "<a href='{$this->baseUri}/destroy/{$data->campaigns_id}' class='btn btn-success btn-xs' title='Sim'>Sim<i class='glyphicon glyphicon-ok-sign'> </i></a> ";
            $StrDelete .= " | ";
            $StrDelete .= " <a href='{$this->baseUri}' class='btn btn-danger btn-xs' title='Não' data-dismiss='alert' aria-hidden='true'>Não<i class='glyphicon glyphicon-remove-sign'></i></a>";

            setMsgFlash('warning', "$StrDelete");
            
            return redirect("{$this->baseUri}");
        }

        public function getDestroy()
        {
            # MODEL
            $data = $this->model->find( $this->params[0] );
            
            # MODEL
            if ( !empty($data->campaigns_id) && $this->model->delete($data->campaigns_id) ){
                setMsgFlash('success', "Registro <strong>{$data->campaigns_title}</strong> foi removido com sucesso!");
                redirect("{$this->baseUri}");
            }else{
                setMsgFlash('danger', "Error ao excluir o registro <strong>{$data->campaigns_title}</strong>! Tente novamente mais tarde.");
                redirect("{$this->baseUri}");
            }
        }



    /**
     * Methods POST
    */
    
        public function postCreate()
        {
            
            #REQUEST
            $dataStore = Container::getServices('FwBD\Request\Request')->post();
            array_pop($dataStore);

            // $code = htmlentities($dataStore['campaigns_codeform']);

            #VALIDATE
            $validate = Container::getServices('FwBD\Validate\Validate','Admin\Campaigns');
            $this->model->setRules(['campaigns_title' => 'requerid | unique:Campaigns | min:3 | max:20'] );
            $validate->validateData($this->model->getRules(), $dataStore);

            if ($validate->getStatus()) {
                setDataInput($dataStore);
                setMsgFlash('warning', $validate->getMessages());
                return redirect("{$this->baseUri}/create");
            }

            $dataStore['campaigns_title']=strtoupper($dataStore['campaigns_title']);
            $dataStore['campaigns_codeform']=htmlentities($dataStore['campaigns_codeform']);
            $dataStore += $this->setDataDefault($dataStore);
            // pp($dataStore,1);

            # MODEL
            if ($this->model->insert($dataStore) ){
                setMsgFlash('success', "O registro <strong>{$dataStore['campaigns_title']}</strong>, foi criado com sucesso!");
                redirect("{$this->baseUri}/create");
            }else{
                setMsgFlash('danger', "Error ao criar novo registro! Favor entrar em contato com suporte.");
                redirect("{$this->baseUri}/create");
            }

        }

        public function postEdit()
        {
            # PARAMS
            /*$page = $this->getParams();
            $id   = $this->getParams('id');
            $url  = $id;*/

            $id   = $this->params[0];

            #REQUEST
            $request = Container::getServices('FwBD\Request\Request');
            $dataformEdit = $request->post();
            array_pop($dataformEdit);

            #VALIDATE
            $validate = Container::getServices('FwBD\Validate\Validate','Admin\Campaigns');
            // $this->model->setRules(['campaigns_title' => 'requerid | unique:Campaigns | min:3 | max:20'] );
            $validate->validateData($this->model->getRules(), $dataformEdit);

            if ($validate->getStatus()) {
                setDataInput($dataformEdit);
                setMsgFlash('warning', $validate->getMessages());
                return redirect("{$this->baseUri}/edit/{$id}");
            }

            # MODEL
            $category = $this->model->find( $id );

            $dataformEdit['campaigns_title']=strtoupper($dataformEdit['campaigns_title']);
            $dataformEdit += $this->setDataDefault($dataformEdit,'edit');

            # MODEL
            if ($this->model->update($category->campaigns_id, $dataformEdit) ){
                setMsgFlash('success', "O registro <strong>{$dataformEdit['campaigns_title']}</strong>, foi editado com sucesso!");
                redirect("{$this->baseUri}/edit/{$id}");
            }else{
                setMsgFlash('danger', "Error ao editar registro! Favor entrar em contato com suporte.");
                redirect("{$this->baseUri}/edit/{$id}");
            }
        }
















    /**
     * Methods GET (FormsCampaigns)
    */
        public function getShowform()
        {
            Container::setFilter(['userGestao']);

            $title = 'Manager Forms Campaigns';
            $params= $this->params[0];
            $data  = $this->modelForms
                ->select('U.user_name, tb_forms.forms_id, tb_forms.forms_name, tb_forms.forms_code, tb_forms.forms_obs, tb_forms.forms_created, tb_forms.forms_updated, tb_forms.forms_status, tb_forms.forms_author')
                ->join('tb_user AS U', 'tb_forms.forms_author = U.user_id')
                ->where('tb_forms.campaigns', $params)
                ->all();

            Container::getView('admin.formsCampaigns.formsCampaigns', compact('title', 'params', 'data'));
        }

        public function getCreateform()
        {
            Container::setFilter(['userGestao']);

            $title = 'Manager Forms Campaigns';
            $params= $this->params[0];
            $data  = [];

            Container::getView('admin.formsCampaigns.formsCampaigns-new-edit', compact('title', 'params', 'data'));
        }

        public function getEditform()
        {
            Container::setFilter(['userGestao']);

            $title  = 'Manager Forms Campaigns';
            $params = $this->params[0];
            $data   = $this->modelForms->find( $this->params[1] );

            Container::getView('admin.formsCampaigns.formsCampaigns-new-edit', compact('title', 'params', 'data'));
        }

    /**
     * Methods POST (FormsCampaigns)
    */
        public function postCreateform()
        {

            $params = $this->params[0];

            #REQUEST
            $dataStore = Container::getServices('FwBD\Request\Request')->post();
            array_pop($dataStore);
            
            // $dataStore['forms_code'] = htmlentities($dataStore['forms_code']);

            #VALIDATE
            $validate = Container::getServices('FwBD\Validate\Validate','Admin\Forms');
            // $this->modelForms->setRules(['level_category' => 'requerid | unique:level | min:3 | max:20'] );
            $validate->validateData($this->modelForms->getRules(), $dataStore);

            if ($validate->getStatus()) {
                setDataInput($dataStore);
                setMsgFlash('warning', $validate->getMessages());
                return redirect("{$this->baseUri}/{$params}/createform");
            }

            $dataStore['forms_code'] = htmlentities($dataStore['forms_code']);
            $dataStore += $this->setDataForms($dataStore);
            // pp($dataStore,1);

            # MODEL
            if ($this->modelForms->insert($dataStore) ){
                setMsgFlash('success', "O registro <strong>{$dataStore['forms_name']}</strong>, foi criado com sucesso!");
                redirect("{$this->baseUri}/{$params}/createform");
            }else{
                setMsgFlash('danger', "Error ao criar novo registro! Favor entrar em contato com suporte.");
                redirect("{$this->baseUri}/{$params}/createform");
            }

        }

        public function postEditform()
        {
            # PARAMS
            $category   = $this->params[0];
            $params_id  = $this->params[1];
            $baseUri    = $this->baseUri.'/'.$category;

            #REQUEST
            $request = Container::getServices('FwBD\Request\Request');
            $dataformEdit = $request->post();
            array_pop($dataformEdit);

            #VALIDATE
            $validate = Container::getServices('FwBD\Validate\Validate','Admin\Forms');
            $this->modelForms->setRules(['campaigns'=>'requerid', 'forms_name'=>'requerid | min:3 | max:20', 'forms_code'=>'requerid | min:1']);
            $validate->validateData($this->modelForms->getRules(), $dataformEdit);

            if ($validate->getStatus()) {
                setDataInput($dataformEdit);
                setMsgFlash('warning', $validate->getMessages());
                return redirect("{$baseUri}/editform/$params_id");
            }

            # MODEL
            $category = $this->modelForms->find( $params_id );

            $dataformEdit['forms_code'] = htmlentities($dataformEdit['forms_code']);
            $dataformEdit += $this->setDataForms($dataformEdit,'edit');

            // pp($category,1);
            // pp($dataformEdit,1);

            # MODEL
            if ($this->modelForms->update($category->forms_id, $dataformEdit) ){
                setMsgFlash('success', "O registro <strong>{$dataformEdit['forms_name']}</strong>, foi editado com sucesso!");
                redirect("{$baseUri}/editform/{$params_id}");
            }else{
                setMsgFlash('danger', "Error ao editar registro! Favor entrar em contato com suporte.");
                redirect("{$baseUri}/editform/{$params_id}");
            }
        }





    
    /**
     * Methods HELPERS
    */
        private function setDataDefault(array $data, $type='')
        {
            $authSession = (object) Container::getSession('get', ['Auth']);

            // $getUri = $data['campaigns_title'];

            if ( empty($type) ) {
                $result = [
                    // 'campaigns_uri'      => cleanString($getUri),
                    'campaigns_created'  => date("Y-m-d H:i:s"),
                    'campaigns_updated'  => date("Y-m-d H:i:s"),
                    'campaigns_status'   => '1',
                    'campaigns_author'   => $authSession->session_user
                ];        
            }else{
                $result = [
                    // 'campaigns_uri'      => cleanString($getUri),
                    'campaigns_updated'  => date("Y-m-d H:i:s"),
                    'campaigns_author'   => $authSession->session_user
                ];
            }

            return $result;
        }

        private function setDataForms(array $data, $type='')
        {
            $authSession = (object) Container::getSession('get', ['Auth']);

            $getUri = $data['forms_name'];

            if ( empty($type) ) {
                $result = [
                    'forms_uri'      => cleanString($getUri),
                    'forms_created'  => date("Y-m-d H:i:s"),
                    'forms_updated'  => date("Y-m-d H:i:s"),
                    'forms_status'   => '1',
                    'forms_author'   => $authSession->session_user
                ];        
            }else{
                $result = [
                    'forms_uri'      => cleanString($getUri),
                    'forms_updated'  => date("Y-m-d H:i:s"),
                    'forms_author'   => $authSession->session_user
                ];
            }

            return $result;
        }








}