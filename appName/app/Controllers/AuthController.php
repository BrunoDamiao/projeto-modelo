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
    // private $pTitle;

    public function __construct($params)
    {
        parent::__construct($params);
        Container::setTemplateView('auth.templates.template');
        $this->model = Container::getServices('App\Models\Auth');

        if ( Container::getSession('has', ['Auth']) )
            redirect('/admin');
    }

    public function __destruct()
    {
        $this->deleteSessionInit();
    }


    /**
     * Methods GET
     */

    public function getIndex()
    {
        $title = 'Auth';
        $data  = self::getJson()['proj_title'];
        Container::getView('auth.auth', compact('title','data'));
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

    /**
     * Methods POST
     */
    public function postAuth()
    {
        #REQUEST
        $request = Container::getServices('FwBD\Request\Request')->post();
        // pp($request,1);

        #VALIDATE
        $validate = Container::getServices('FwBD\Validate\Validate','Auth');
        $this->model->setRules([
            'user_email' => 'requerid | email | min:2 | max:15',
            'user_password' => 'min:4 | max:20',
        ] );
        $validate->validateData($this->model->getRules(), $request);

        if ($validate->getStatus()) {
            setDataInput($request);
            setMsgFlash('warning', $validate->getMessages());
            return redirect('/auth');
        }

        #CRIPTOGRAFA A PASS
        $request['user_password'] = Encrypt::hashCode($request['user_password']);
        $request['user_status'] = 1;

        $this->model
            ->setTable('tb_user AS auth')
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

    public function postForgotIn()
    {

        #REQUEST
        $request = Container::getServices('FwBD\Request\Request')->post();
        // pp($request,1);

        #VALIDATE
        $validate = Container::getServices('FwBD\Validate\Validate','Auth');
        $this->model->setRules(['user_email' => 'requerid | email | min:2 | max:15']);
        $validate->validateData($this->model->getRules(), $request);

        if ($validate->getStatus()) {
            setDataInput($request);
            setMsgFlash('warning', $validate->getMessages());
            return redirect('/auth');
        }

        # AUTENTICANDO OS DADOS
        $data = $this->model
                    ->where('user_email', $request['user_email'])
                    ->all()
                    ->getResult()[0];

        if ( !$data ) {
            setMsgFlash('danger', "Não foi encontrado nenhum utilizador com este endereço de email.");
            redirect('/auth');
        }else{
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
                $nome  = $data->user_name;
                $email = $data->user_email;

                $assunto = 'Senha Alterada'; # Wp+IAzDx

                $message  = '
                    <!DOCTYPE html>
                    <html lang="pt-br">
                      <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
                      <title>'.strtoupper($this->pTitle).'</title>
                    </head>
                    <body>';
                // $message .= 'favicon: '.APP_ROOT.DS.PATH_FAVICON;
                $message .= '<div style="width: 640px; font-family: Arial, Helvetica, sans-serif; font-size: 14px;">
                        <h1>'.CONFIG_EMAIL['EMPRESA'].'</h1>
                        <p>Olá '.$nome.',</p>
                        <p>Sua senha de login foi alterada para <strong><a href="http://localhost:8080/auth" title="acessar sistema" target="_blank">'.$NewPass.'</strong>. Se você acha que isso seja um erro, envie um e-mail para <strong>'.CONFIG_EMAIL['MAIL'].'<strong>, onde você poderá entrar em contato com nossa equipe de suporte.</p> <br>
                        <p>Atenciosamente,</p>
                        <p><strong>'.CONFIG_EMAIL['EMPRESA'].'</strong><br>
                        '.CONFIG_EMAIL['SITE'].'<br>
                      '.CONFIG_EMAIL['CONTATO'].'</p>
                    </div>
                    </body>
                    </html>
                ';
                $altBody = CONFIG_EMAIL['EMPRESA']."\n
                      Olá ".$nome."\n,
                      Sua senha de login foi alterada para ".$NewPass.". Se você acha que isso seja um erro, envie um e-mail para '.SP_EMAIL.', onde você poderá entrar em contato com nossa equipe de suporte.\n\n
                      Atenciosamente,\n
                      ".CONFIG_EMAIL['EMPRESA']."\n
                      ".CONFIG_EMAIL['SITE']." \n
                      ".CONFIG_EMAIL['CONTATO']." \n
                ";

            $mail = Email::smtpEmail($nome, $email, $assunto, $altBody, $message);

            $msgFeed = '<strong> <i class="fa fa-fw fa-lg fa-check-circle"></i>Esqueci a senha!</strong> <br> Você deverá receber em breve um e-mail com a senha redefinida. Por favor, certifique-se de verificar seus spams e lixo se você não encontrar o e-mail. '.$NewPass;
            if ($mail) {
                setMsgFlash('success', $msgFeed);
                redirect('/auth');
            }else{
                setMsgFlash('danger', "Falha no envio do e-mail, tentar novamente mais tarde!");
                redirect('/auth');
            }

        }

    }

    public function postForgot()
    {
        #REQUEST
        $request = Container::getServices('FwBD\Request\Request')->post();
        // array_pop($request);
        pp($request,1);

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

}