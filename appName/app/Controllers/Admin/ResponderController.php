<?php
namespace App\Controllers\Admin;

use FwBD\Controller\BaseController;
use FwBD\DI\Container;

class ResponderController extends BaseController
{
    private $model;
    private $baseUri   = '/admin/leads';
    private $totalPage = APP_PAGINATOR;    


    public function __construct($params)
    {
        parent::__construct($params);
        Container::setFilter(['auth']);
        Container::setTemplateView('admin.templates.template');
        $this->model = Container::getServices('App\Models\Admin\Responder');
    }



    
    /**
     * Methods GET
    */
    
        public function getIndex()
        {
            Container::setFilter(['userGestao']);

            $title = 'Manager Responder';
            
            // $data  = [];
            
            $data  = $this->model
                ->select('tb_leads.leads_id, leads_name, leads_email, leads_uri, leads_created, leads_updated, leads_status, leads_author, 
                    group_concat(C.campaigns_title separator "<br> ") as campaigns')
                ->join('tb_campaigns_leads AS CL', 'CL.leads_id = tb_leads.leads_id', 'LEFT JOIN')
                ->join('tb_campaigns AS C', 'C.campaigns_id = CL.campaigns_id', 'LEFT JOIN')
                ->groupBy('tb_leads.leads_id')
                ->all();

            Container::getView('admin.responder.responder', compact('title','data'));
        }

        public function getJstatus()
        {
            # REQUEST
            $request = Container::getServices('FwBD\Request\Request');
            $data = $request->get();

            # MODEL
            $leads = $this->model->find( $data['id'] );
            $dataStatus['leads_status'] = ($leads->leads_status == 1)? 0 : 1;

            # UPDATE
            if ($this->model->update($leads->leads_id, $dataStatus) ){

                if ( $leads->leads_status == 1 ) {
                    $alertClas = 'alert-warning';
                    $alertStr  = 'desativado';
                }else{
                    $alertClas = 'alert-success';
                    $alertStr  = 'ativado';
                }

                echo json_encode([
                    'msg_alert' => $alertClas, 
                    'msg_text'  => 'O registro ' . $leads->leads_email.', foi <strong>'.$alertStr.'</strong> com sucesso!', 
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

            $title = 'Manager Leads';
            $data  = [];
            $campaigns = Container::getServices('App\Models\Admin\Campaigns')
                ->where('campaigns_status','1')
                ->all()
                ->getResult();
            // pp($campaigns,1);

            Container::getView('admin.leads.leads-new-edit', compact('title','data','campaigns'));
        }

        public function getEdit()
        {
            Container::setFilter(['userGestao']);

            $title = 'Manager Leads';
            
            $data  = $this->model
                ->select('tb_leads.leads_id, leads_name, leads_email, leads_uri, leads_created, leads_updated, leads_status, leads_author, 
                    group_concat(C.campaigns_id separator ";") as campaigns')
                ->join('tb_campaigns_leads AS CL', 'CL.leads_id = tb_leads.leads_id', 'LEFT JOIN')
                ->join('tb_campaigns AS C', 'C.campaigns_id = CL.campaigns_id', 'LEFT JOIN')
                ->where('tb_leads.leads_id', $this->params[0])
                ->groupBy('tb_leads.leads_id')
                ->all()
                ->getResult();

            $campaigns = Container::getServices('App\Models\Admin\Campaigns')
                ->where('campaigns_status','1')
                ->all()
                ->getResult();

            Container::getView('admin.leads.leads-new-edit', compact('title','data','campaigns'));
        }

        public function getDelete()
        {
            # MODEL
            $data = $this->model->find( $this->params[0] );

            $StrDelete  = "Atenção! Deseja excluir o registro <strong>{$data->leads_email}</strong> permanentemente? ";

            $StrDelete .= "<a href='{$this->baseUri}/destroy/{$data->leads_id}' class='btn btn-success btn-xs' title='Sim'>Sim<i class='glyphicon glyphicon-ok-sign'> </i></a> ";
            $StrDelete .= " | ";
            $StrDelete .= " <a href='{$this->baseUri}' class='btn btn-danger btn-xs' title='Não' data-dismiss='alert' aria-hidden='true'>Não<i class='glyphicon glyphicon-remove-sign'></i></a>";

            setMsgFlash('warning', "$StrDelete");
            
            return redirect("{$this->baseUri}");
        }

        public function getDestroy()
        {
            # MODEL
            $data = $this->model->find( $this->params[0] );

            /**
             * OBS: Foreign key leads_id da tb_campaigns_leads
             * foi editada na OPTIONS: On Delete: CASCADE
             * Ao deletar lead, será deletar automaticamente o relacionamento associado;
             */
            
            # MODEL
            if ( !empty($data->leads_id) && $this->model->delete($data->leads_id) ){
                setMsgFlash('success', "Registro <strong>{$data->leads_email}</strong> foi removido com sucesso!");
                redirect("{$this->baseUri}");
            }else{
                setMsgFlash('danger', "Error ao excluir o registro <strong>{$data->leads_email}</strong>! Tente novamente mais tarde.");
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

            #VALIDATE
            $validate = Container::getServices('FwBD\Validate\Validate','Admin\leads');
            $validate->validateData($this->model->getRules(), $dataStore);

            if ($validate->getStatus()) {
                setDataInput($dataStore);
                setMsgFlash('warning', $validate->getMessages());
                return redirect("{$this->baseUri}/create");
            }

            $dataStore += $this->setDataDefault($dataStore);
            // pp($dataStore, 1);

            # MODEL
            $lastID = $this->model->insert($dataStore, 'getlastID');

            if ( ! empty($dataStore['campaigns']) ) {

                foreach ($dataStore['campaigns'] as $k => $campaigns) {
                    $dates = date('Y-m-d H:i');
                    $createRelationship = 'INSERT INTO tb_campaigns_leads (campaigns_id, leads_id, cpld_created, cpld_updated) ';
                    $createRelationship .= 'VALUES( "'.$campaigns.'", "'.$lastID.'", "'.$dates.'", "'.$dates.'" )';
                    $relationship = \FwBD\Model\BaseModel::exec($createRelationship);

                    if ( $relationship == false ) {
                        setMsgFlash('warning', "Error ao criar relacionamento entre as tabelas! Favor entrar em contato com suporte.");
                        return redirect("{$this->baseUri}/create");
                    }
                }

            }else
                $relationship = true;

            if ( $relationship == true ) {
                setMsgFlash('success', "O registro <strong>{$dataStore['leads_email']}</strong>, foi criado com sucesso!");
                redirect("{$this->baseUri}/create");
            }else{
                setMsgFlash('danger', "Error ao criar novo registro! Favor entrar em contato com suporte.");
                redirect("{$this->baseUri}/create");
            }

        }

        public function postCreateSend()
        {
            
            #REQUEST
            $dataStore = Container::getServices('FwBD\Request\Request')->post();
            // array_pop($dataStore);
            // pp($dataStore, 1);

            #VALIDATE
            $validate = Container::getServices('FwBD\Validate\Validate','Admin\leads');
            $validate->validateData($this->model->getRules(), $dataStore);

            if ($validate->getStatus()) {
                setDataInput($dataStore);
                setMsgFlash('warning', $validate->getMessages());
                return redirect("/");
            }

            $dataStore += $this->setDataDefault($dataStore);
            // pp($dataStore, 1);

            # MODEL
            $lastID = $this->model->insert($dataStore, 'getlastID');

            if ( ! empty($dataStore['campaigns']) ) {

                foreach ($dataStore['campaigns'] as $k => $campaigns) {
                    $dates = date('Y-m-d H:i');
                    $createRelationship = 'INSERT INTO tb_campaigns_leads (campaigns_id, leads_id, cpld_created, cpld_updated) ';
                    $createRelationship .= 'VALUES( "'.$campaigns.'", "'.$lastID.'", "'.$dates.'", "'.$dates.'" )';
                    $relationship = \FwBD\Model\BaseModel::exec($createRelationship);

                    if ( $relationship == false ) {
                        setMsgFlash('warning', "Error ao criar relacionamento entre as tabelas! Favor entrar em contato com suporte.");
                        return redirect("/");
                    }
                }

            }else
                $relationship = true;

            if ( $relationship == true ) {
                setMsgFlash('success', "O registro <strong>{$dataStore['leads_email']}</strong>, foi criado com sucesso!");
                redirect("/");
            }else{
                setMsgFlash('danger', "Error ao criar novo registro! Favor entrar em contato com suporte.");
                redirect("/");
            }

        }

        public function postEdit()
        {
            
            $id = $this->params[0];
            $uriEdit = $this->baseUri.'/edit/'.$id;

            #REQUEST
            $dataEdit = Container::getServices('FwBD\Request\Request')->post();
            array_pop($dataEdit);

            #VALIDATE
            $validate = Container::getServices('FwBD\Validate\Validate','Admin\leads');
            $this->model->setRules(['leads_email' => 'requerid | email | min:3 | max:20']);
            $validate->validateData($this->model->getRules(), $dataEdit);

            if ($validate->getStatus()) {
                setDataInput($dataEdit);
                setMsgFlash('warning', $validate->getMessages());
                return redirect($uriEdit);
            }

            # MODEL
            $leads     = $this->model->find( $id );
            $dataEdit += $this->setDataDefault($dataEdit,'edit');

            if ( ! empty($dataEdit['campaigns']) && $this->editRelationship($leads->leads_id, $dataEdit['campaigns']) == false ) {
                setMsgFlash('warning', "Error ao criar relacionamento entre as tabelas! Favor entrar em contato com suporte.");
                return redirect($uriEdit);
            }

            if ( $this->model->update($leads->leads_id, $dataEdit) ) {                    
                setMsgFlash('success', "O registro <strong>{$dataEdit['leads_email']}</strong> foi editado com sucesso!");
                return redirect($uriEdit);                
            }else{
                setMsgFlash('danger', "Error ao editar registro! Favor entrar em contato com suporte.");
                redirect($uriEdit);
            }

        }





    /**
     * Methods HELPERS
    */
        private function setDataDefault(array $data, $type='')
        {
            $authSession = (object) Container::getSession('get', ['Auth']);

            $getUri = $data['leads_name'];

            if ( empty($type) ) {
                $result = [
                    'leads_uri'      => cleanString($getUri),
                    'leads_created'  => date("Y-m-d H:i:s"),
                    'leads_updated'  => date("Y-m-d H:i:s"),
                    'leads_status'   => '1',
                    'leads_author'   => $authSession->session_user
                ];        
            }else{
                $result = [
                    'leads_uri'      => cleanString($getUri),
                    'leads_updated'  => date("Y-m-d H:i:s"),
                    'leads_author'   => $authSession->session_user
                ];
            }

            return $result;
        }


        private function editRelationship($id, $data)
        {

            $modelCL = Container::getServices('App\Models\Admin\Campaigns_Leads');

            $getData = $modelCL
                ->select('campaigns_id')
                ->where('leads_id', $id)
                ->all()
                ->getResult();

            // pp($data);
            // pp($getData,1);

            // if ( count($data) !== count($getData) ) {
            if ( ! in_array($getData, $data) ) {

                #DELETE Relationships
                $modelCL->delete($id);

                foreach ($data as $k => $campaigns) {

                    $dataCL = [
                        'campaigns_id' => $campaigns,
                        'leads_id'     => $id,
                        'cpld_created' => date('Y-m-d H:i'),
                        'cpld_updated' => date('Y-m-d H:i'),
                    ];

                    if ( ! $modelCL->insert($dataCL) )
                        return false;

                }

                return true;
            }
            return true;

        }



}