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
    private $config;


    public function __construct($params)
    {
        parent::__construct($params);
        // Container::setFilter(['SetupOut']);
        // Container::setTemplateView('setup.templates.template');
        // $this->model = Container::getServices('App\Models\Auth');
        $this->config = DB_CONNECTIONS; //(DB_CONFIG)?? $connections;
    }


    /**
     * Methods GET
     */

    public function getIndex()
    {
        $title = 'Settings DB';

        $data = $this->config;
        // pp($data,1);

        Container::getView('setup.settings', compact('title','data'));
    }



    /**
     * Methods POST
     */
        // public function postCreateSqlite()
        public function postDbCreate()
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
                if ( $this->addSQLiteScript() ) {
                    setMsgFlash('success', 'A conexão com database '. $data['DBName'] .' foi estabelecida com sucesso!');
                }else{
                    pp('error addSQLiteScript',1);
                }
            }

            # Add datas default [tb_level, tb_user]
            setMsgFlash('info', 'O usuário default foi criado com sucesso!');

            pp('final',1);
            // redirect('/auth');

        }



    /**
     * Methods HELPERS
     */

        private  function getConfigBD($data)
        {

            if ( $data['DBDrive'] === 'sqlite' ) {

                $dbhost = substr_replace($data['DBHost'], '', strlen(PATH_DATABASE));
                $dbname = explode('.', $data['DBName']);
                $rs = [
                    // $data['DBDrive'] => [
                        'DBDrive'     => $data['DBDrive'],
                        'DBHost'      => $dbhost . $dbname[0] . '.db',
                        'DBName'      => $dbname[0] . '.db',
                        'DBUser'      => '-',
                        'DBPass'      => '-',
                        'DBCharset'   => '-',
                        'DBCollation' => '-'
                    // ],
                ];

            }else{
                $rs = $data;
                /*$rs = [
                    $data['DBDrive'] => $data
                ];*/
            }
            return $rs;

        }

        private function addSQLiteScript()
        {

            # $password = Encrypt::hashCode('masterkey');
            $dataUser     = 'admin';
            $dataEmail    = 'masterkey@mk.com';
            $dataPassword = '76861ae9ac3aa8a79a21e392d771e2a6b46c7a15a6aae32035c6d5c8547b7bf8e06dc3874d04a1dad703f3061de039c3d053a61db7d44c3636c9d96d5c433c59';
            $dataDate    = date('Y-m-d H:i');


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












            /**
             * execute script sql in database: SQLite3
             */

            /*
                $config = getJsonDBConfig(PATH_STORAGE . 'database' .DS );
                $dbh = \FwBD\DBConect\DBConect::getCon($config);

                $stmt = $dbh->prepare($SCRIPT);
                $datas = [
                            ':level_category'=> 'MASTERKEY',
                            ':level_name'    => '--',
                            ':level_obs'     => 'obs masterkey',
                            ':level_uri'     => 'masterkey',
                            ':level_created' => $dataDate,
                            ':level_updated' => $dataDate,
                            ':level_status'  => '1',
                            ':level_author'  => '0',
                            ':level_id'      => '1',
                            ':user_name'     => $dataUser,
                            ':user_email'    => $dataEmail,
                            ':user_password' => $dataPassword,
                            ':user_show'     => 'masterkey',
                            ':user_thumb'    => '--',
                            ':user_obs'      => 'obs masterkey',
                            ':user_uri'      => 'masterkey',
                            ':user_created'  => $dataDate,
                            ':user_updated'  => $dataDate,
                            ':user_status'   => '1',
                            ':user_author'   => '0' ];
                // $qryIns = $this->conPDO->prepare($sqlIns);
            try {

                $stmt->bindValue(':level_category', 'MASTERKEY');
                $stmt->bindValue(':level_name',     '--');
                $stmt->bindValue(':level_obs',      'obs masterkey');
                $stmt->bindValue(':level_uri'     , 'masterkey');
                $stmt->bindValue(':level_created' , "$dataDate");
                $stmt->bindValue(':level_updated' , "$dataDate");
                $stmt->bindValue(':level_status'  , '1');
                $stmt->bindValue(':level_author'  , '0');
                $stmt->bindValue(':level_id'      , '1');

                /*$stmt->bindParam(':user_name'     , "$dataUser");
                $stmt->bindParam(':user_email'    , "$dataEmail");
                $stmt->bindParam(':user_password' , "$dataPassword");
                $stmt->bindParam(':user_show'     , 'masterkey');
                $stmt->bindParam(':user_thumb'    , '--');
                $stmt->bindParam(':user_obs'      , 'obs masterkey');
                $stmt->bindParam(':user_uri'      , 'masterkey');
                $stmt->bindParam(':user_created'  , "$dataDate");
                $stmt->bindParam(':user_updated'  , "$dataDate");
                $stmt->bindParam(':user_status'   , '1');
                $stmt->bindParam(':user_author'   , '0' );*/

                /*foreach ($datas as $key => $value) {
                    // echo "$key > $value <br>";
                    // $stmt->bindValue($key, $value);
                    $stmt->bindParam("{$key}", $value);
                    // $stmt->bindValue(":{$key}", $value);
                }*/
                // exit();
                // $dbh->beginTransaction();
                //$r = $stmt->execute();
                // $r = $stmt->execute( $datas );
                // $dbh->commit();

                /*pp($r,1);

            } catch (PDOException $e) {
                // $dbh->rollback();
                // pp('error exce');
                pp($e->getMessage());
            }*/
            /*
            $config = getJsonDBConfig(PATH_STORAGE . 'database' .DS );
            $pdo = \FwBD\DBConect\DBConect::getCon($config);

            // $r = ( $pdo->exec($SCRIPT) )? 'true' : 'false' ;
            $r = $pdo->exec($SCRIPT);
            pp($r,1);
            // return
            */

        }




}