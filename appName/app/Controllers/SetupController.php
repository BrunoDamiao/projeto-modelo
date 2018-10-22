<?php
namespace App\Controllers;

use FwBD\Controller\BaseController;
use FwBD\DI\Container;
use FwBD\DBConect\DBConect as DBConect;
use FwBD\Encrypt\Encrypt;
use FwBD\Mail\Email;
use FwBD\Json\Json;

class SetupController extends BaseController
{
    private $config;
    private $pdo;

    public function __construct($params)
    {
        parent::__construct($params);

        // Container::setFilter(['SetupTab']);
        Container::setTemplateView('setup.templates.template');
        $this->config = DB_CONNECTIONS; //(DB_CONFIG)?? $connections;

    }


    /**
     * Methods GET
     */

    public function getIndex()
    {
        // Container::setFilter(['SetupTab']);
        Container::setFilter(['SetupTab'=>0]);
        $title = 'Settings DataBase';
        $data = $this->config;

        Container::getView('setup.settings', compact('title','data'));
    }

    public function getCreateMaster()
    {
        // $fprm = ['1'=>'/setup/create-master'];
        $fprm = ['1'];
        Container::setFilter(['SetupTab'=>1]);
        $title = 'Settings Tables';

        $data = []; //$this->getConPDO()->query("SELECT * FROM tb_user")->fetch();
        // pp($data,1);

        Container::getView('setup.create-master', compact('title','data'));
    }



    /**
     * Methods POST
     */
        /**
         * Função responsável por criar o arquivo.json de conexão com banco de dados;
         * @return redirect('/create-master')
         */
        public function postCreateConexao()
        {
            # Request datas forms
            $request = new \FwBD\Request\Request;
            $rs = $request->post();

            # Validate datas forms
            $validate = new \FwBD\Validate\Validate;
            $rules = [
                'DBDrive'     => 'requerid | min:2 | max:255',
                'DBHost'      => 'requerid | min:2 | max:255',
                'DBName'      => 'requerid | min:2 | max:255',
                'DBUser'      => 'requerid | min:2 | max:255',
                'DBPass'      => 'requerid | min:2 | max:255',
                'DBCharset'   => 'requerid | min:2 | max:255',
                'DBCollation' => 'requerid | min:2 | max:255'
            ];
                $validate->validateData($rules, $rs);
                if ($validate->getStatus()) {
                    setDataInput($rs);
                    setMsgFlash('warning', $validate->getMessages());
                    return redirect("/settings");
                }

            # Retorna array com datas de acordo com drive de banco de dados;
            $data = $this->getConfigBD($rs);

            # Monta string path do arquivo.json (file conexão com db)
            if ( $rs['DBDrive'] === 'sqlite' ) {
                $jshost = substr_replace($rs['DBHost'], '', strlen(PATH_DATABASE));
                $jsname = explode('.', $rs['DBName']);
                $path = $jshost . $jsname[0] . '.json';
            }else{
                $path = PATH_DATABASE . $rs['DBName'] . '.json';
            }

            # Checa se já existe fileConDB.json e remove o file;
            foreach (glob($path."*.json") as $file) {
                if (file_exists($file)) {
                    \FwBD\Json\Json::deleteJson($path);
                }
            }

            # Checa fileConDB.json foi criado, redireciona com msg de erro;
            if ( !\FwBD\Json\Json::createJson($data, $path) ) {
                setMsgFlash('warning', 'Error! Não foi possível criar o file [fileConDB.json] de configuração do database.');
                return redirect('/settings');
            }

            /**
             * Caso tudo tenha ocorrido com sucesso, redireciona p/ pagina de criação das
             * tabelas + insert datas com mensagem de sucesso ao criar fileCon.Json
             */
            /*if ( !$this->dbInsertDatas() )
                setMsgFlash('warning', 'Error! Não foi possível criar o file [fileConDB.json] de configuração do database.');
                redirect('/setup/create-master');*/

            return redirect('/setup/create-master');

        }

        /**
         * Editar usuário master
         * @return redirect('/auth/logout')
         */
        public function postCreateMaster()
        {
            # Cria table e add user master
            if ( !$this->dbInsertDatas() ) {
                setMsgFlash('warning', 'Error! Não foi possível criar tabelas do sistema no database.');
                return redirect('/setup/create-master');
            }

            # Request datas forms
            $request = new \FwBD\Request\Request;
            $rs = $request->post();
            pp($rs);

            # Validate datas forms
            $validate = new \FwBD\Validate\Validate;
            $rules = [
                'user_name' => 'requerid | min:3 | max:20',
                'user_email' => 'requerid | email | min:2 | max:15',
                'user_password' => 'min:4 | max:12',
            ];
                $validate->validateData($rules, $rs);
                if ($validate->getStatus()) {
                    setDataInput($rs);
                    setMsgFlash('warning', $validate->getMessages());
                    return redirect("/settings");
                }

            $user = $this->getConPDO()->query("SELECT * FROM tb_user WHERE user_id = '1'")->fetch();
            pp($user);

            # Processador Password Encrypt
            $encryptForm = \FwBD\Encrypt\Encrypt::hashCode($rs['user_password']);
            if ( $user->user_password === $encryptForm ) {
                unset($rs['user_password']);
                unset($rs['user_show']);
            }else{
                $rs['user_show']     = $rs['user_password'];
                $rs['user_password'] = $encryptForm;
            }

            $rs += [
                'user_uri'      => cleanString($rs['user_name']),
                'user_updated'  => date("Y-m-d H:i:s"),
            ];
            pp($rs);

            $update = "UPDATE tb_user "
                    . "SET user_name = {$rs['user_name']}, "
                    . "user_email    = {$rs['user_email']} "
                    . "user_password = {$rs['user_password']} "
                    . "user_show     = {$rs['user_show']} "
                    . "user_uri      = {$rs['user_uri']} "
                    . "user_updated  = {$rs['user_updated']} "
                . "WHERE user_id = {$user->user_id} ";
            $this->getConPDO()->exec($update);

            return redirect('/auth/logout');
        }




    /**
     * Methods HELPERS
     */

        /**
         * Define os dados de configuração para cada DRIVE
         * @return array dados de configuração do banco
         */
        private  function getConfigBD($data)
        {

            if ( $data['DBDrive'] === 'sqlite' ) {

                $dbhost = substr_replace($data['DBHost'], '', strlen(PATH_DATABASE));
                $dbname = explode('.', $data['DBName']);
                $rs = [
                    'DBDrive'     => $data['DBDrive'],
                    'DBHost'      => $dbhost . $dbname[0] . '.db',
                    'DBName'      => $dbname[0] . '.db',
                    'DBUser'      => '-',
                    'DBPass'      => '-',
                    'DBCharset'   => '-',
                    'DBCollation' => '-'
                ];

            }else{
                $rs = $data;
            }
            return $rs;

        }

        /**
         * Realiza a conexão com banco via PDO
         * @return PDO
         */
        private function getConPDO()
        {
            $dbConfig = getJsonDBConfig(PATH_STORAGE . 'database' .DS);
            return DBConect::getCon( $dbConfig );
        }

        /**
         * Executa a SQL criando as tabelas Levels e Users
         * @return boolean
         */
        private function dbCreateTables()
        {
            /**
             * create tb_level, tb_user default
             */
            $pdo = $this->getConPDO();
            try {

                $pdo->exec('
                    CREATE TABLE IF NOT EXISTS tb_level ( level_id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE, level_category TEXT NOT NULL, level_name TEXT NOT NULL, level_obs TEXT, level_uri TEXT NOT NULL, level_created TEXT, level_updated TEXT, level_status INTEGER(3) DEFAULT 1, level_author INTEGER(3) DEFAULT 1 );
                    CREATE TABLE IF NOT EXISTS tb_user ( user_id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE, level_id INTEGER NOT NULL, user_name TEXT NOT NULL, user_email TEXT NOT NULL, user_password TEXT NOT NULL, user_show TEXT, user_thumb TEXT, user_obs TEXT, user_uri TEXT NOT NULL, user_created TEXT, user_updated TEXT, user_status INTEGER(3) DEFAULT 1, user_author INTEGER(3) DEFAULT 1, FOREIGN KEY (level_id) REFERENCES tb_level(level_id) ON UPDATE CASCADE ON DELETE CASCADE);
                ');
                return true;

            } catch (PDOException $e) {
                echo 'Error! dbCreateTables() '.$e->getMessage();
            }
        }

        /**
         * Executa a SQL add datas nas tabelas Levels e Users
         * @return boolean
         */
        private function dbInsertDatas()
        {

            $sqlLevel = 'INSERT INTO tb_level (level_id, level_category, level_name, level_obs, level_uri, level_created, level_updated, level_status, level_author)' . 'VALUES (:level_id, :level_category, :level_name, :level_obs, :level_uri, :level_created, :level_updated, :level_status, :level_author); ';
            $dtLevel = [
                ':level_category'   => 'cat masterkey',
                ':level_name'       => 'name masterkey',
                ':level_obs'        => 'obs masterkey',
                ':level_uri'        => 'uri-masterkey',
                ':level_created'    => date('Y-m-d H:i'),
                ':level_updated'    => date('Y-m-d H:i'),
                ':level_status'     => 1,
                ':level_author'     => 0
            ];

            $sqlUser = 'INSERT INTO tb_user (level_id, user_name, user_email, user_password, user_show, user_thumb, user_obs, user_uri, user_created, user_updated, user_status, user_author)' . 'VALUES (:level_id, :user_name, :user_email, :user_password, :user_show, :user_thumb, :user_obs, :user_uri, :user_created, :user_updated, :user_status, :user_author); ';
            $dtUser = [
                ':level_id'         => 1,
                ':user_name'        => 'admin',
                ':user_email'       => 'masterkey@mk.com',
                ':user_password'    => '76861ae9ac3aa8a79a21e392d771e2a6b46c7a15a6aae32035c6d5c8547b7bf8e06dc3874d04a1dad703f3061de039c3d053a61db7d44c3636c9d96d5c433c59',
                ':user_show'        => 'masterkey',
                ':user_thumb'       => '--',
                ':user_obs'         => 'obs masterkey',
                ':user_uri'         => 'uri-masterkey',
                ':user_created'     => date('Y-m-d H:i'),
                ':user_updated'     => date('Y-m-d H:i'),
                ':user_status'      => 1,
                ':user_author'      => 0
            ];

            $pdo = $this->getConPDO();
            try {
                $pdo->beginTransaction();
                    $this->dbCreateTables();

                    $stmt = $pdo->prepare($sqlLevel);
                        $stmt->execute($dtLevel);
                    $stmt = $pdo->prepare($sqlUser);
                        $stmt->execute($dtUser);
                $pdo->commit();
                return true;
            } catch (PDOException $e) {
                echo 'Error! dbInsertDatas() '.$e->getMessage();
                $pdo->rollback();
            }

        }













        private function addSQLiteScriptXXXXX()
        {

            # $password = Encrypt::hashCode('masterkey');
            $datas = [
                'DBUser'  => 'admin',
                'DBEmail' => 'masterkey@mk.com',
                'DBText'  => 'masterkey',
                'DBPass'  => '76861ae9ac3aa8a79a21e392d771e2a6b46c7a15a6aae32035c6d5c8547b7bf8e06dc3874d04a1dad703f3061de039c3d053a61db7d44c3636c9d96d5c433c59',
                'DBDate'  => "date('Y-m-d H:i')",
            ];

            $prmConfigDB = getJsonDBConfig(PATH_STORAGE . 'database' .DS );
            $pdo = \FwBD\DBConect\DBConect::getCon($prmConfigDB);


            /**
             * create tb_level, tb_user default
             */
            $SCRIPT  =  'CREATE TABLE IF NOT EXISTS "tb_level" (
                            `level_id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
                            `level_category` TEXT NOT NULL,
                            `level_name` TEXT NOT NULL,
                            `level_obs` TEXT,
                            `level_uri` TEXT NOT NULL,
                            `level_created` TEXT,
                            `level_updated` TEXT,
                            `level_status` INTEGER(3) DEFAULT 1,
                            `level_author` INTEGER(3) DEFAULT 1
                        ); ';

            $SCRIPT .=  'CREATE TABLE IF NOT EXISTS "tb_user" (
                            `user_id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
                            `level_id` INTEGER NOT NULL,
                            `user_name` TEXT NOT NULL,
                            `user_email` TEXT NOT NULL,
                            `user_password` TEXT NOT NULL,
                            `user_show` TEXT,
                            `user_thumb` TEXT,
                            `user_obs` TEXT,
                            `user_uri` TEXT NOT NULL,
                            `user_created` TEXT,
                            `user_updated` TEXT,
                            `user_status` INTEGER(3) DEFAULT 1,
                            `user_author` INTEGER(3) DEFAULT 1
                        ); ';

            /**
             * create level, user default: MASTERKEY
             */
            // 8 colum
            $SCRIPTz = "INSERT INTO `tb_level` (
                            `level_category`,
                            `level_name`,
                            `level_obs`,
                            `level_uri`,
                            `level_created`,
                            `level_updated`,
                            `level_status`,
                            `level_author`,
                           ) VALUES (
                            ':level_category',
                            ':level_name',
                            ':level_obs',
                            ':level_uri',
                            ':level_created',
                            ':level_updated',
                            ':level_status',
                            ':level_author',
                        ); ";
            // 12
            $SCRIPTx = "INSERT INTO `tb_user` (
                            `level_id`,
                            `user_name`,
                            `user_email`,
                            `user_password`,
                            `user_show`,
                            `user_thumb`,
                            `user_obs`,
                            `user_uri`,
                            `user_created`,
                            `user_updated`,
                            `user_status`,
                            `user_author`
                           )VALUES (
                            ':level_id',
                            ':user_name',
                            ':user_email',
                            ':user_password',
                            ':user_show',
                            ':user_thumb',
                            ':user_obs',
                            ':user_uri',
                            ':user_created',
                            ':user_updated',
                            ':user_status',
                            ':user_author'
                        ); ";

        }

        public function postDbCreateXXXXX()
        {
            $request = new \FwBD\Request\Request;
            $rs = $request->post();

            $validate = new \FwBD\Validate\Validate;
            $rules = [
                'DBDrive'     => 'requerid | min:2 | max:255',
                'DBHost'      => 'requerid | min:2 | max:255',
                'DBName'      => 'requerid | min:2 | max:255',
                'DBUser'      => 'requerid | min:2 | max:255',
                'DBPass'      => 'requerid | min:2 | max:255',
                'DBCharset'   => 'requerid | min:2 | max:255',
                'DBCollation' => 'requerid | min:2 | max:255'
            ];
            $validate->validateData($rules, $rs);

            if ($validate->getStatus()) {
                setDataInput($rs);
                setMsgFlash('warning', $validate->getMessages());
                return redirect("/setup");
            }


            $data = $this->getConfigBD($rs);
            if ( $rs['DBDrive'] === 'sqlite' ) {
                $jshost = substr_replace($rs['DBHost'], '', strlen(PATH_DATABASE));
                $jsname = explode('.', $rs['DBName']);
                $path = $jshost . $jsname[0] . '.json';
            }else{
                $path = PATH_DATABASE . $rs['DBName'] . '.json';
            }
            // pp($rs);
            // pp($data,1);
            // pp($path);

            foreach (glob($path."*.json") as $file) {
                if (file_exists($file)) {
                    \FwBD\Json\Json::deleteJson($path);
                }
            }

            if ( !\FwBD\Json\Json::createJson($data, $path) ) {
                setMsgFlash('warning', 'Error! Não foi possível criar o arquivo de configuração do database.');
                // redirect('/auth');
            }

            // pp($data,1);
            # Cria as tabs [tb_level, tb_user]
            if ( $data['DBDrive'] === 'sqlite' ) {

                pp($this->dbCreateTables());
                pp('Criou table sqlite');

                pp($this->dbInsertDatas());
                pp('insert datas tb_level e tb_user',1);

                /*if ( $this->addSQLiteScript() ) {
                    setMsgFlash('success', 'A conexão com database '. $data['DBName'] .' foi estabelecida com sucesso!');
                }else{
                    pp('error addSQLiteScript',1);
                }*/
            }

            # Add datas default [tb_level, tb_user]
            setMsgFlash('info', 'O usuário default foi criado com sucesso!');

            pp('final',1);
            // redirect('/auth');

        }

}