<?php
namespace App\Controllers\Admin;

use FwBD\Controller\BaseController;
use FwBD\DI\Container;

class WebformsController extends BaseController
{
    
    private $model;
    private $baseUri   = '/admin/webforms';
    private $totalPage = APP_PAGINATOR; 


    public function __construct($params)
    {
        parent::__construct($params);
        Container::setFilter(['auth']);
        Container::setTemplateView('admin.templates.template');
        $this->model = Container::getServices('App\Models\Admin\Webforms');
    }

    /**
     * Methods GET
    */

        public function getIndex()
        {
            Container::setFilter(['userGestao']);

            $title = 'Manager WebForms';

            $data = $this->model
                ->select('forms_id, campaigns, forms_name, forms_code, forms_obs, forms_uri, forms_created, forms_updated, forms_status, forms_author, user_name, campaigns_title')
                ->join('tb_user AS U', 'forms_author = user_id')
                ->join('tb_campaigns AS C', 'campaigns = campaigns_id')
                ->all();


            Container::getView('admin.webforms.webforms', compact('title','data'));
        }

        public function getJstatus()
        {
            # REQUEST
            $request = Container::getServices('FwBD\Request\Request');
            $data = $request->get();

            # MODEL
            $forms = $this->model->find( $data['id'] );
            $dataStatus['forms_status'] = ($forms->forms_status == 1)? 0 : 1;

            # UPDATE
            if ($this->model->update($forms->forms_id, $dataStatus) ){

                if ( $forms->forms_status == 1 ) {
                    $alertClas = 'alert-warning';
                    $alertStr  = 'desativado';
                }else{
                    $alertClas = 'alert-success';
                    $alertStr  = 'ativado';
                }

                echo json_encode([
                    'msg_alert' => $alertClas, 
                    'msg_text'  => 'O registro ' . $forms->forms_name.', foi <strong>'.$alertStr.'</strong> com sucesso!', 
                ],false);

            }else{

                echo json_encode(['msg_alert' => 'alert-danger', 
                    'msg_text'  => 'Error ao mudar o status do registro! Favor entrar em contato com suporte.', 
                ],false);

            }
        }

        public function getCreate()
        {}

        public function getEdit()
        {
            Container::setFilter(['userGestao']);

            $title = 'Manager WebForms';
            $params = $this->params[0];
            $data  = $data = $this->model
                ->select('forms_id, campaigns, forms_name, forms_code, forms_obs, forms_uri, forms_created, forms_updated, forms_status, forms_author, user_name, campaigns_title')
                ->join('tb_user AS U', 'forms_author = user_id')
                ->join('tb_campaigns AS C', 'campaigns = campaigns_id')
                ->where('forms_id', $this->params[0])
                ->all()
                ->getResult();

            Container::getView('admin.webforms.webforms-new-edit', compact('title','data','params'));
        }

        public function getDelete()
        {

        }

        public function getDestroy()
        {

        }




    /**
     * Methods POST
    */

        public function postEdit()
        {
            # PARAMS
            // $category   = $this->params[0];
            $params_id  = $this->params[0];
            $baseUri    = $this->baseUri.'/edit';

            #REQUEST
            $request = Container::getServices('FwBD\Request\Request');
            $dataformEdit = $request->post();
            array_pop($dataformEdit);

            // $dataformEdit['forms_code'] = htmlentities($dataformEdit['forms_code']);
            // pp($dataformEdit,1);

            #VALIDATE
            $validate = Container::getServices('FwBD\Validate\Validate','Admin\Forms');
            $this->model->setRules(['campaigns'=>'requerid', 'forms_name'=>'requerid | min:3 | max:20', 'forms_code'=>'requerid | min:1']);
            $validate->validateData($this->model->getRules(), $dataformEdit);

            if ($validate->getStatus()) {
                setDataInput($dataformEdit);
                setMsgFlash('warning', $validate->getMessages());
                return redirect("{$baseUri}/$params_id");
            }

            # MODEL
            $category = $this->model->find( $params_id );

            $dataformEdit['forms_code'] = htmlentities($dataformEdit['forms_code']);
            $dataformEdit += $this->setDataDefault($dataformEdit,'edit');

            // pp($category);
            // pp($dataformEdit,1);

            # MODEL
            if ($this->model->update($category->forms_id, $dataformEdit) ){
                setMsgFlash('success', "O registro <strong>{$dataformEdit['forms_name']}</strong>, foi editado com sucesso!");
                redirect("{$baseUri}/{$params_id}");
            }else{
                setMsgFlash('danger', "Error ao editar registro! Favor entrar em contato com suporte.");
                redirect("{$baseUri}/{$params_id}");
            }
        }

    


    
    /**
     * Methods HELPERS
    */
    
        private function setDataDefault(array $data, $type='')
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