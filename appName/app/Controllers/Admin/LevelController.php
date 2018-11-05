<?php
namespace App\Controllers\Admin;

use FwBD\Controller\BaseController;
use FwBD\DI\Container;

class LevelController extends BaseController
{

    private $model;
    private $baseUri   = '/admin/level';
    private $totalPage = APP_PAGINATOR;


    public function __construct($params)
    {
        parent::__construct($params);
        Container::setFilter(['Auth']);
        Container::setTemplateView('admin.templates.template');
        $this->model = Container::getServices('App\Models\Admin\Level');
    }

    /**
     * Methods GET
    */

        public function getIndex()
        {
            // Container::setFilter(['userGestao']);
            // $authSession = (object) Container::getSession('get', ['Auth']);

            $title = 'Manager Levels';

            $data = $this->model
                ->distinct()
                ->select('tb_level.level_id, tb_level.level_category, tb_level.level_name, tb_level.level_obs, tb_level.level_uri, tb_level.level_created, tb_level.level_updated, tb_level.level_status, tb_level.level_author, tb_user.user_name')
                ->join('tb_user','tb_user.user_id = tb_level.level_author' )
                ->where('tb_level.level_category', 'MASTERKEY', '!=')
                ->where('tb_level.level_name', '--', '!=')
                ->all()
                ->getResult();

            Container::getView('admin.level.level', compact('title','data'));
        }

        public function getJstatus()
        {
            # REQUEST
            $request = Container::getServices('FwBD\Request\Request');
            $data = $request->get();

            # MODEL
            $level = $this->model->find( $data['id'] );
            $dataStatus['level_status'] = ($level->level_status == 1)? 0 : 1;

            # UPDATE
            if ($this->model->update($level->level_id, $dataStatus) ){

                if ( $level->level_status == 1 ) {
                    $alertClas = 'alert-warning';
                    $alertStr  = 'desativado';
                }else{
                    $alertClas = 'alert-success';
                    $alertStr  = 'ativado';
                }

                echo json_encode(['msg_alert' => $alertClas,
                    'msg_text'  => 'O registro ' . $level->level_name.', foi <strong>'.$alertStr.'</strong> com sucesso!',
                ],false);

            }else{

                echo json_encode(['msg_alert' => 'alert-danger',
                    'msg_text'  => 'Error ao mudar o status do registro! Favor entrar em contato com suporte.',
                ],false);

            }
        }

        public function getCreate()
        {
            $title  = 'Manager Levels';
            $data   = [];
            $category = $this->model
                ->select('tb_level.level_id, tb_level.level_category, tb_level.level_name, tb_level.level_obs')
                ->where('tb_level.level_category', 'MASTERKEY', '!=')
                ->where('level_name','--')
                ->all()
                ->getResult();

            Container::getView('admin.level.level-new-edit', compact('title','data','category'));
        }

        public function getEdit()
        {
            // Container::setFilter(['userGestao']);

            $title = 'Manager Levels';
            $data = $this->model->find( $this->params[0] );
            $category = $this->model
                ->select('tb_level.level_id, tb_level.level_category, tb_level.level_name, tb_level.level_obs')
                ->where('tb_level.level_category', 'MASTERKEY', '!=')
                ->where('level_name','--')
                ->all()
                ->getResult();
            Container::getView('admin.level.level-new-edit', compact('title','data','category'));
        }

        public function getDelete()
        {
            # MODEL
            $data = $this->model->find( $this->params[0] );

            $StrDelete  = "Atenção! Deseja excluir o registro <strong>{$data->level_category}</strong> permanentemente? ";

            $StrDelete .= "<a href='{$this->baseUri}/destroy/{$data->level_id}' class='btn btn-success btn-xs' title='Sim'>Sim<i class='glyphicon glyphicon-ok-sign'> </i></a> ";
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
            if ( !empty($data->level_id) && $this->model->delete($data->level_id) ){
                setMsgFlash('success', "Registro <strong>{$data->level_category}</strong> foi removido com sucesso!");
                redirect("{$this->baseUri}");
            }else{
                setMsgFlash('danger', "Error ao excluir o registro <strong>{$data->level_category}</strong>! Tente novamente mais tarde.");
                redirect("{$this->baseUri}");
            }
        }



    /**
     * Methods POST
     */
        public function postCreate()
        {
            #REQUEST
            $dataStore = Container::getServices('FwBD\Request\Request')->all();
            // array_pop($dataStore);
            // pp($dataStore,1);

            #VALIDATE
            $validate = Container::getServices('FwBD\Validate\Validate','Admin\Level');
            $this->model->setRules(['level_name' => 'requerid | min:3 | max:20'] );
            $validate->validateData($this->model->getRules(), $dataStore);

            if ($validate->getStatus()) {
                setDataInput($dataStore);
                setMsgFlash('warning', $validate->getMessages());
                return redirect("{$this->baseUri}/create");
            }

            $dataStore += $this->setDataDefault($dataStore);
            // pp($dataStore,1);

            # MODEL
            if ($this->model->insert($dataStore) ){
                setMsgFlash('success', "O registro <strong>{$dataStore['level_name']}</strong> da categoria {$dataStore['level_category']}, foi criado com sucesso!");
                redirect("{$this->baseUri}/create");
            }else{
                setMsgFlash('danger', "Error ao criar novo registro! Favor entrar em contato com suporte.");
                redirect("{$this->baseUri}/create");
            }
        }

        public function postEdit()
        {
            # PARAMS
            $id   = $this->params[0];

            #REQUEST
            $request = Container::getServices('FwBD\Request\Request');
            $dataformEdit = $request->post();
            array_pop($dataformEdit);

            #VALIDATE
            $validate = Container::getServices('FwBD\Validate\Validate','Admin\Level');
            $this->model->setRules([
                'level_category' => 'requerid | min:1 | max:120',
                'level_name' => 'requerid | min:3 | max:120',
            ]);
            $validate->validateData($this->model->getRules(), $dataformEdit);

            if ($validate->getStatus()) {
                setDataInput($dataformEdit);
                setMsgFlash('warning', $validate->getMessages());
                return redirect("{$this->baseUri}/edit/{$id}");
            }

            # MODEL
            $category = $this->model->find( $id );

            $dataformEdit += $this->setDataDefault($dataformEdit,'edit');
            // pp($dataformEdit,1);


            # MODEL
            if ($this->model->update($category->level_id, $dataformEdit) ){
                setMsgFlash('success', "O registro <strong>{$dataformEdit['level_name']}</strong> da categoria {$dataformEdit['level_category']}, foi editado com sucesso!");
                redirect("{$this->baseUri}/edit/{$id}");
            }else{
                setMsgFlash('danger', "Error ao editar registro! Favor entrar em contato com suporte.");
                redirect("{$this->baseUri}/edit/{$id}");
            }
        }




    /**
     * Methods MODALS
     */
    public function postListLevel()
    {
        #REQUEST
        $dataStore = Container::getServices('FwBD\Request\Request')->post();

        $authSession = (object) Container::getSession('get', ['Auth']);

        $data = Container::getServices('App\Models\Admin\level')
            ->select('tb_level.level_id, tb_level.level_category,
                tb_level.level_name')
            // ->where('level_name', '--')
            // ->where('level_category', 'MASTERKEY', '!=')
            ->where('level_name', '--', '!=')
            ->all()
            ->getResult();

        // header('Content-Type: application/json');
        $output = json_encode($data,true);
        echo $output;
    }

    public function postNewLevel()
    {

        #REQUEST
        $dataStore = Container::getServices('FwBD\Request\Request')->post();

        $dataStore['level_name'] = (!empty($dataStore['level_name']))?
            $dataStore['level_name'] : '--' ;

        #VALIDATE
        $validate = Container::getServices('FwBD\Validate\Validate','Admin\Level');
        $this->model->setRules([
            'level_category' => 'requerid | min:3 | max:20',
            'level_name' => 'requerid | unique:level | min:3 | max:20',
        ]);
        $validate->validateData($this->model->getRules(), $dataStore);

        if ($validate->getStatus()) {

            $output = [
                'data'      => [],
                'msg_alert' => 'alert-warning',
                'msg_text'  => $validate->getMessages(),
            ];

            $output = json_encode($output,false);
            echo $output;
            exit();
        }

        $dataStore['level_name'] = (!empty($dataStore['level_name']))?
            $dataStore['level_name'] : '--' ;
        $getUri = $dataStore['level_category'].' '.$dataStore['level_name'];

        $authSession = (object) Container::getSession('get', ['Auth']);
        $dataStore += [
            'level_uri'      => cleanString($getUri),
            'level_created'  => date("Y-m-d H:i:s"),
            'level_updated'  => date("Y-m-d H:i:s"),
            'level_status'   => 1,
            'level_author'   => $authSession->session_user,
        ];
        // pp($dataStore,1);

        if ( $this->model->insert($dataStore) ){

            $dataFind = $this->model
            ->select('level_id, level_category, level_name')
            ->where('level_category', 'MASTERKEY', '!=')
            ->where('level_name', '--', '!=')
            ->all()
            ->getResult();

            echo json_encode([
                    'data'      => $dataFind,
                    'msg_alert' => 'alert-success',
                    'msg_text'  => 'O registro <strong>'.strtoupper($dataStore['level_uri']).'</strong>, foi criado com sucesso!',
                ],false);
                exit();

        }else{

            echo json_encode([
                    'data'      => [],
                    'msg_alert' => 'alert-warning',
                    'msg_text'  => 'Não possível gravar o registro <strong>'.strtoupper($dataStore['level_uri']).'</strong>',
                ],false);

        }
    }



    /**
     * Methods HELPERS
     */

    private function setDataDefault(array $data, $type='')
    {
        $authSession = (object) Container::getSession('get', ['Auth']);

        $getUri = $data['level_category'].' '.$data['level_name'];

        if ( empty($type) ) {
            $result = [
                'level_uri'      => cleanString($getUri),
                'level_created'  => date("Y-m-d H:i:s"),
                'level_updated'  => date("Y-m-d H:i:s"),
                'level_status'   => '1',
                'level_author'   => $authSession->session_user
            ];
        }else{
            $result = [
                'level_uri'      => cleanString($getUri),
                'level_updated'  => date("Y-m-d H:i:s"),
                'level_author'   => $authSession->session_user
            ];
        }

        return $result;
    }








}