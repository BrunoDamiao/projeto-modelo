<?php
namespace App\Controllers;

use FwBD\Controller\BaseController;
use FwBD\DI\Container;
use FwBD\DBConect\DBConect;
use FwBD\Encrypt\Encrypt;
use FwBD\Mail\Email;
use FwBD\Json\Json;

class AuthController extends BaseController
{
    private $model;


    public function __construct($params)
    {
        parent::__construct($params);
        # Create dataBase com tb_User
        // Container::setFilter(['createDB']);
        // Container::setFilter(['SetupSystem']);
        Container::setTemplateView('auth.templates.template');
        $this->model = Container::getServices('App\Models\Auth');

        $this->deleteSessionInit();
    }

    public function __destruct()
    {
        $this->deleteSessionInit();
        // Container::getSession('delete', ['Auth']);
    }


    /**
     * Methods GET
     */

    public function getIndex()
    {
        $title = 'Auth';
        Container::getView('auth.auth', compact('title'));
    }

    public function getCreate()
    {
        $title = 'Create';
        Container::getView('auth.create', compact('title'));

        // $this->getView('Cadastra-se Agora!', 'Create');
        // Container::getView('auth.Create', ['title' => 'Cadastra-se Agora!']);
    }

    public function getForgot()
    {
        $title = 'Forgot';
        Container::getView('auth.forgot', compact('title'));

        // $this->getView('Esqueceu a Senha?', 'forgot');
        // Container::getView('auth.forgot', ['title' => 'Esqueceu a Senha?']);
    }

    public function getLogout()
    {
        if ( !Container::getSession('has', ['Auth']) )
            return redirect('/auth');


        #AUTHSET, set flag=0, deixando o usuario Off-line
        $ssAuth = Container::getSession('get', ['Auth']);
        // $AuthSet = $this->model->update($ssAuth['session_user'], ['user_auth' => 0]);

        #AUTHSET, del JOSN SESSAO, deixando o usuario Off-line
        Json::delete($ssAuth['session_user']);

        Container::getSession('delete', ['Auth']);

        redirect('/auth');
    }

    # Page setup systems #
    public function getSettings()
    {
        echo "string";
        /*$title = 'Settings Systems';
        Container::getView('Settings', compact('title'));*/
    }


    /**
     * Methods POST
     */
    public function postAuth()
    {

        #REQUEST
        $request = Container::getServices('FwBD\Request\Request')->post();
        // array_pop($request);

        #VALIDATE
        $validate = Container::getServices('FwBD\Validate\Validate','Auth');
        $this->model->setRules([
            'user_email' => 'requerid | email | min:2 | max:15',
            'user_password' => 'min:4 | max:20',
        ] );
        $validate->validateData($this->model->getRules(), $request);

        pp('oi '.$validate->getStatus(),1);

        if ($validate->getStatus()) {
            setDataInput($request);
            setMsgFlash('warning', $validate->getMessages());
            return redirect('/auth');
        }


        #CRIPTOGRAFA A PASS
        $request['user_password'] = Encrypt::hashCode($request['user_password']);
        $request['user_status'] = 1;

        $this->model
            ->select('auth.user_id, auth.user_name, auth.user_email, auth.user_password, auth.user_show, auth.user_thumb, auth.user_status, l.level_category, l.level_name')
            ->join('tb_level as l', 'l.level_id = auth.level_id')
            ->where('user_email', $request['user_email'])
            ->where('user_password', $request['user_password'])
            ->where('auth.user_status', '1')
            ->all();
        $datas = $this->model->getResult();

        #VALIDA 00: USER SESSION - Verifica se sessao auth existe
        if ( Container::getSession('has', ['Auth']) ){
            $session = Container::getSession('get', ['Auth']);
            if ( $request['user_email'] == $session['session_user_email'] )
                redirect('/admin');
        }

        #VALIDANDO 01: DB
        if ( empty($datas) ) {
            setMsgFlash('danger', "Por favor, verifique seu e-mail e sua senha estão correto.");
            return redirect('/auth');
        }

        #VALIDANDO 02: Json
        if ( Json::has() ) {
            $dataJson = Json::get();
            // pp($dataJson,1);
            foreach ($dataJson as $k => $v) {

                if ( !empty($dataJson) AND $dataJson[$k]['session_user'] == $datas[0]->user_id ) {
                    setMsgFlash('danger', "Usuário logado no sistema! Acesse com outra conta de usuário.");
                    return redirect('/auth');
                }

            }
        }

        if ( $this->newSession($datas) )
            redirect('/admin');

    }

    public function postCreate()
    {

        #REQUEST
        $request = Container::getServices('FwBD\Request\Request')->post();
        array_pop($request);

        #VALIDATE
        $validate = Container::getServices('FwBD\Validate\Validate','Auth');
        $this->model->setRules([
            'user_name' => 'requerid | unique:user | min:3 | max:20',
            'user_email' => 'requerid | email | unique:user | min:2 | max:15',
            'user_password' => 'requerid | min:4 | max:8']);
        $validate->validateData($this->model->getRules(), $request);

        if ($validate->getStatus()) {
            setDataInput($request);
            setMsgFlash('warning', $validate->getMessages());
            return redirect('/auth/create');
        }
        // pp(($request['profile'])?? 1);
        // pp($request);

        #DATA FORM
        $datas = [
            'user_name'     => $request['user_name'],
            'user_email'    => $request['user_email'],
            'user_password' => Encrypt::hashCode($request['user_password']),
            'user_show'     => $request['user_password'],
            'user_thumb'    => '',
            'user_obs'      => '--',
            'user_uri'      => cleanString($request['user_name']),
            'user_created'  => date('Y-m-d H:i'),
            'user_updated'  => date('Y-m-d H:i'),
            'user_status'   => 1,
            'user_author'   => 2
        ];

        # SAVE REGISTRO DB
        if ( $this->model->insert($datas) ) {

            $userLog = $this->model
                        ->select('user_id, user_name, user_auth')
                        ->where('user_name', $datas['user_name'])
                        ->all()
                        ->getResult();

            if ( $this->newSession($userLog) ) {
                setMsgFlash('success', "Parabéns <strong>{$userLog[0]->user_name}</strong>, você foi cadastrado com sucesso!");
                redirect('/admin');
            }

        }else{
            setMsgFlash('danger', "Não foi possivel realizar o cadastro, tente novamente mais tarde.");
            redirect('/auth/create');
        }

    }

    public function postCreateBoss()
    {

        # EXEC sql, criando user MASTERKEY nas tbs level, user e level_user

        /*$levelBoss = $this->getSuperLevel();
        ($levelBoss,1);*/

        #REQUEST
        $request = Container::getServices('FwBD\Request\Request')->post();
        array_pop($request);

        #VALIDATE
        $validate = Container::getServices('FwBD\Validate\Validate','Auth');
        $this->model->setRules([
            'user_name' => 'requerid | unique:user | min:3 | max:20',
            'user_email' => 'requerid | email | unique:user | min:2 | max:15',
            'user_password' => 'requerid | min:4 | max:8']);
        $validate->validateData($this->model->getRules(), $request);

        if ($validate->getStatus()) {
            setDataInput($request);
            setMsgFlash('warning', $validate->getMessages());
            return redirect('/auth/create');
        }

        #DATA FORM
        $datas = [
            'user_name'     => $request['user_name'],
            'user_email'    => $request['user_email'],
            'user_password' => Encrypt::hashCode($request['user_password']),
            'user_show'     => $request['user_password'],
            'user_thumb'    => '',
            'user_obs'      => '--',
            'user_uri'      => cleanString($request['user_name']),
            'user_created'  => date('Y-m-d H:i'),
            'user_updated'  => date('Y-m-d H:i'),
            'user_status'   => 1,
            'user_author'   => 2
        ];

        pp($datas,1);

        # SAVE REGISTRO DB
        if ( $this->model->insert($datas) ) {

            $userLog = $this->model
                        ->select('user_id, user_name, user_auth')
                        ->where('user_name', $datas['user_name'])
                        ->all()
                        ->getResult();

            if ( $this->newSession($userLog) ) {
                setMsgFlash('success', "Parabéns <strong>{$userLog[0]->user_name}</strong>, você foi cadastrado com sucesso!");
                redirect('/admin');
            }

        }else{
            setMsgFlash('danger', "Não foi possivel realizar o cadastro, tente novamente mais tarde.");
            redirect('/auth/create');
        }

    }

    public function postForgot()
    {

        #REQUEST
        $request = Container::getServices('FwBD\Request\Request')->post();
        array_pop($request);

        #VALIDATE
        $validate = Container::getServices('FwBD\Validate\Validate','Auth');
        $this->model->setRules(['user_email' => 'requerid | email | min:2 | max:15']);
        $validate->validateData($this->model->getRules(), $request);

        if ($validate->getStatus()) {
            setDataInput($request);
            setMsgFlash('warning', $validate->getMessages());
            return redirect('/auth/forgot');
        }

        # AUTENTICANDO OS DADOS
        $data = $this->model
                    ->where('user_email', $request['user_email'])
                    ->all()
                    ->getResult()[0];

        if ( $data ) {
            ## Gera uma nova Senha aleatória de 4 a 8 digito.
            $NewPass = $this->gerar_senha(8);
            $NewPassCode = Encrypt::hashCode($NewPass);

            $dataPassword = [
                'user_password' => $NewPassCode,
                'user_show'     => $NewPass
            ];

            ## Atualiza nova senha do User no banco
            $this->model->update($data->user_id, $dataPassword);

            ## Disparar Email com a NOVA SENHA.
                $nome = $data->user_name;
                $email = $data->user_email;

                $assunto = EMAIL_LEMBRETE['assunto'];
                $altBody = EMAIL_LEMBRETE['altBody'];

                $message  = '';
                $message .= EMAIL_LEMBRETE['msgTitle'];
                $message .= "<p> Sua NOVA Senha de acesso é: <strong> <a href='";
                $message .= PATH_HOME.'/auth';
                $message .= "' target='_blank'>";
                $message .= "{$NewPass}";
                $message .= "</a></strong> <br>";
                $message .= " Válida por 30 segundos! </p> ";
                $message .= EMAIL_LEMBRETE['msgAtt'];

            // $mail = Email::smtpEmail($nome, $email, $assunto, $altBody, $message);
            $mail = \FwBD\Mail\Email::smtpEmail($nome, $email, $assunto, $altBody, $message);

            if ($mail) {
                setMsgFlash('success', "Sua NOVA SENHA foi enviado para sua caixa de e-mail informado a baixo. $NewPass");
                redirect('/auth/forgot');
            }else{
                setMsgFlash('danger', "Falha no envio do e-mail, tentar novamente mais tarde!");
                redirect('/auth/forgot');
            }

        }else{
            setMsgFlash('danger', "Este e-mail não está cadastrado no sistema.");
            redirect('/auth/forgot');
        }

    }



    /**
     * Methods HELPERS
     */

        private function getView(string $title, $view=null)
        {
            $title = ($title)?? "Empty title";
            $viewRender = ($view)?? strtolower($title);

            $data = Container::getServices('App\Models\Admin\Campaigns')
                        ->setTable('tb_campaigns AS C')
                        ->select('C.campaigns_id, C.campaigns_title,
                            forms_id, campaigns, forms_name, forms_code')
                        ->join('tb_forms AS F', 'C.campaigns_id = F.campaigns')
                        ->all()
                        ->getResult();

            Container::getView('auth.'.$viewRender, compact('title','data'));
        }

        private function gerar_senha($tamanho, $maiusculas=true, $minusculas=true, $numeros=true, $simbolos=true)
        {

            $senha = '';
            $ma = "ABCDEFGHIJKLMNOPQRSTUVYXWZ"; // $ma contem as letras maiúsculas
            $mi = "abcdefghijklmnopqrstuvyxwz"; // $mi contem as letras minusculas
            $nu = "0123456789"; // $nu contem os números
            $si = "!@#$%¨&*()_+="; // $si contem os símbolos

            if ($maiusculas){
                // se $maiusculas for "true", a variável $ma é embaralhada e adicionada para a variável $senha
                $senha .= str_shuffle($ma);
            }

            if ($minusculas){
                // se $minusculas for "true", a variável $mi é embaralhada e adicionada para a variável $senha
                $senha .= str_shuffle($mi);
            }

            if ($numeros){
                // se $numeros for "true", a variável $nu é embaralhada e adicionada para a variável $senha
                $senha .= str_shuffle($nu);
            }

            if ($simbolos){
                // se $simbolos for "true", a variável $si é embaralhada e adicionada para a variável $senha
                $senha .= str_shuffle($si);
            }

            // retorna a senha embaralhada com "str_shuffle" com o tamanho definido pela variável $tamanho
            return substr(str_shuffle($senha),0,$tamanho);
        }


        private function newSession($datas)
        {

            $data = (array) $datas[0];

            #AUTHSET, set flag=1, deixando o usuario Online
            // pp($data,1);
            $dataSession = [
                'session_key'        => session_id(),
                'session_create'     => date('Y-m-d H:i:s'),
                'session_timeEnd'    => $this->createTimeEnd(),
                'session_user'       => $data['user_id'],
                'session_user_name'  => $data['user_name'],
                'session_user_thumb' => $data['user_thumb'],
                'session_category'   => $data['level_category'],
                'session_level'      => $data['level_name']
            ];
            // pp($dataSession,1);

            #AUTHSET, criando JOSN SESSAO, deixando o usuario Online
            Json::create($dataSession);

            Container::getSession('set', ['Auth', $dataSession]);

            return true;

        }

        private function createTimeEnd($addTime='')
        {
            if ( empty($_SESSION['Auth']) ) {

                $time = !empty($addTime)? $addTime : EXPIRE_SESSION_AUTH;
                $newDataEnd = time() + $time;
                $dataEnd = date("Y-m-d H:i:s", $newDataEnd);

                // pp($dataEnd,1);
                return $dataEnd;

            }
        }

        /**
         * Só deleta Json e Session, se a data atual for maior que data End;
         */
        private function deleteSessionInit()
        {

            if ( Json::has() ) {
                $dataJson = Json::get();
                // pp($dataJson,1);

                foreach ($dataJson as $k => $v) {
                    // pp($v['session_timeEnd']);
                    $dataFinal = new \DateTime($v['session_timeEnd']);
                    $dataNow = new \DateTime('now'); # hora atual
                    $diff = $dataNow->diff($dataFinal);

                    if (!empty($diff) && $diff->invert == 1) {

                        Json::delete($v['session_user']);
                        Container::getSession('delete', ['Auth']);
                        // return "delete json e session";
                        // pp("delete json e session",1);

                    }

                }

            }

        }




        # Funcção inválido no sistema - só para exemplo
        private function createSuperUserXXXX()
        {

            # BOSS
            $userBoss = $this->model->all();
            pp($userBoss,1);

            if ( empty($userBoss->getTotal()) ) {

                # Create Level MasterKey
                $sqlLevel  = 'INSERT INTO tb_level (level_category, level_subcategory, level_obs, level_uri, level_created, level_updated, level_status, level_author) ';
                $sqlLevel .= "VALUES( 'MASTERKEY', 'MASTERKEY', 'user super MASTERKEY', 'MASTERKEY', '".date('Y-m-d H:i')."', '".date('Y-m-d H:i')."', '1', '0' )";

                $execLevel = \FwBD\Model\BaseModel::exec($sqlLevel);

                # Create User MasterKey
                $sqlUser  = 'INSERT INTO tb_user (user_name, user_email, user_password, user_show, user_thumb, user_obs, user_uri, user_created, user_updated, user_status, user_author) ';
                $sqlUser .= "VALUES( 'masterkey', 'masterkey@masterkey.com', '".Encrypt::hashCode('masterkey')."', 'masterkey', '', 'obs masterkey', 'masterkey', '".date('Y-m-d H:i')."', '".date('Y-m-d H:i')."', '1', '0' )";

                $execUser = \FwBD\Model\BaseModel::exec($sqlUser);

                if ( ($execLevel == true) AND ($execUser == true) ) {

                }

                $level = Container::getServices('App\Models\Admin\Level')
                ->where('tb_level.level_category', 'MASTERKEY')->all()->getResult()[0];
                $user = $this->model->where('auth.user_name', 'masterkey')->all()->getResult()[0];

                # Create Level_User MasterKey
                $sqlLevelUser = "INSERT INTO tb_level_user (level_id, user_id) VALUES('{$level->level_id}','{$user->user_id}')";
                $execUser = \FwBD\Model\BaseModel::exec($sqlLevelUser);
            }

        }





}