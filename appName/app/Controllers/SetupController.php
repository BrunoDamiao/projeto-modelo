<?php
namespace App\Controllers;

use FwBD\Controller\BaseController;
use FwBD\DI\Container;
use FwBD\DBConect\DBConect as DBConect;
use FwBD\Encrypt\Encrypt as Encrypt;
use FwBD\Mail\Email;
use FwBD\Json\Json;

class SetupController extends BaseController
{
    private $config;
    private $pdo;

    public function __construct($params)
    {
        parent::__construct($params);

        Container::setFilter(['SetupOut']);
        Container::setTemplateView('setup.templates.template');
        // $this->config = DB_CONNECTIONS; //(DB_CONFIG)?? $connections;
        // pp(DB_CONNECTIONS,1);

        $configDefault = [
            'dbasesDefault'     => DB_CONNECTIONS,
            'userDefault'       => [
                'level_id'      => '3',
                'level_name'    => 'Admin',
                'user_name'     => SP_NAME,
                'user_email'    => SP_EMAIL,
                'user_password' => SP_PASS
            ],
            'projectDefault'    => [
                'proj_type'     => APP_TYPE,
                'proj_key'      => APP_KEY,
                'proj_title'    => APP_TITLE,
                'proj_slogn'    => APP_SLOGAN,
                'proj_midias'   => PATH_MIDIAS,
                'proj_paginator'=> APP_PAGINATOR,
            ],
        ];

        $this->config = $configDefault;
        // pp($this->config,1);
    }


    /**
     * Methods GET
     */

    public function getIndex()
    {
        $title = 'Settings DataBase';
        $data = $this->config;
        // pp($data,1);

        Container::getView('setup.setup', compact('title','data'));
    }


    /**
     * Methods POST
     */
        /**
         * Função responsável por criar o arquivo.json de conexão com banco de dados;
         * @return redirect('/auth')
         */
        public function postSetup()
        {
            # Request datas forms
            $request = new \FwBD\Request\Request;
            $rs = $request->post();
            // pp($rs);

            $data = $this->getDataForm($rs);
            // pp($data,1);

            # Validate datas forms
            $validate = new \FwBD\Validate\Validate;
            $rules = [
                'drive'     => 'requerid | min:2 | max:255',
                'host'      => 'requerid | min:2 | max:255',
                'name'      => 'requerid | min:2 | max:255',
                'user'      => 'requerid | min:2 | max:255',
                'password'  => 'requerid | min:2 | max:255',
                'charset'   => 'requerid | min:2 | max:255',
                'collation' => 'requerid | min:2 | max:255',
                'user_name' => 'requerid | min:2 | max:255',
                'user_email' => 'requerid | email | min:2 | max:255',
                'user_password' => 'requerid | min:2 | max:255',
                'proj_category' => 'requirid | min:1 | max:255',
                'proj_key'      => 'requirid | min:2 | max:255',
                'proj_title'    => 'requirid | min:2 | max:255',
                'proj_slogan'   => 'requirid | min:2 | max:255',
                'proj_midias'   => 'requirid | min:2 | max:255',
                'proj_paginator'=> 'requirid | min:1 | max:255',
            ];
                $validate->validateData($rules, $data);
                if ($validate->getStatus()) {
                    setDataInput($data);
                    setMsgFlash('warning', $validate->getMessages());
                    return redirect("/setup");
                }
            // pp($data,1);

            # Monta string path do arquivo.json (file conexão com db)
            if ( $data['drive'] === 'sqlite' ) {
                $host         = PATH_ROOT . $this->cleanName($rs['sqlite_host']);
                $name         = $this->cleanName($rs['sqlite_name']);
                $data['host'] = $host . $name . '.db';
                $path         = $host . $name . '.json';
            }else{
                $path = PATH_DATABASE . $this->cleanName($data['name']) . '.json';
            }
            // pp($data);
            // pp($path,1);

            if ( !$this->execScript($data) )
                setMsgFlash('warning', 'Error! Não foi possível criar o usuário masterkey do database.');
                redirect('/setup');

            # Checa se já existe fileConDB.json e remove o file;
            foreach (glob($path."*.json") as $file) {
                if (file_exists($file))
                    \FwBD\Json\Json::deleteJson($path);
            }

            # Checa fileConDB.json foi criado, redireciona com msg de erro;
            if ( !\FwBD\Json\Json::createJson($data, $path) ) {
                setMsgFlash('warning', 'Error! Não foi possível criar o file [fileConDB.json] de configuração do database.');
                return redirect('/setup');
                exit();
            }

            setMsgFlash('success', 'Parabéns! A conexão com database foi criado com sucesso.');
            redirect('/auth');

        }



    /**
     * Methods HELPERS
     */

        /**
         * Define os dados de configuração para cada DRIVE
         * @return array dados de configuração do banco
         */
        private function getDataForm(array $rs)
        {
            if ( $rs['DBDrive'] === 'sqlite-tab' ) {
                $dt['drive']    = 'sqlite';
                $dt['host']     = $rs['sqlite_host'];
                $dt['name']     = $this->cleanName($rs['sqlite_name']);
                $dt['user']     = $rs['sqlite_attr1'];
                $dt['password'] = $rs['sqlite_attr2'];
                $dt['charset']  = $rs['sqlite_attr3'];
                $dt['collation']= $rs['sqlite_attr4'];
            }elseif ( $rs['DBDrive'] === 'mysql-tab' ) {
                $dt['drive']    = 'mysql';
                $dt['host']     = $rs['mysql_host'];
                $dt['name']     = $this->cleanName($rs['mysql_name']);
                $dt['user']     = $rs['mysql_user'];
                $dt['password'] = $rs['mysql_password'];
                $dt['charset']  = $rs['mysql_charset'];
                $dt['collation']= $rs['mysql_collation'];
            }

            $dt['level_id']      = $rs['level_id'];
            $dt['level_name']    = $rs['level_name'];
            $dt['user_name']     = $rs['user_name'];
            $dt['user_email']    = $rs['user_email'];
            $dt['user_password'] = $rs['user_password'];

            $dt['proj_category']= $rs['proj_category'];
            $dt['proj_key']     = $rs['proj_key'];
            $dt['proj_title']   = $rs['proj_title'];
            $dt['proj_slogan']  = $rs['proj_slogan'];
            $dt['proj_midias']  = $rs['proj_midias'];
            $dt['proj_paginator'] = $rs['proj_paginator'];

            return $dt;
        }

        /**
         * Realiza a conexão com banco via PDO
         * @return PDO
         */
        private function getConPDO(array $dbConfig)
        {
            $dbConfig['init'] = 1;
            $pdo = \FwBD\DBConect\DBConect::getCon($dbConfig);
            if ( $pdo )
                return $pdo;
        }

        private function execScript(array $config)
        {
            $pdo = $this->getConPDO($config);

            if ( $config['drive'] === 'sqlite' ) :
                $script  = $this->sqlTableSqlite($config['name']);
                # $script .= $this->sqlInsertSqlite($config);
            endif;

            if ( $config['drive'] === 'mysql' ) :
                $script  = $this->sqlTableMysql($config['name']);
                # $script .= $this->sqlInsertMysql($config);
            endif;

            $script .= $this->sqlInsertData($config);

            try {
                $pdo->beginTransaction();
                    $pdo->exec($script);
                $pdo->commit();
                return true;
            } catch (PDOException $e) {
                $pdo->rollback();
                return $e;
            }

        }


        /**
         * Script Create table e Insert Datas Mysql
         */
        private function sqlInsertData(array $datas)
        {
            $data     = $datas;
            $db       = $data['name'];
            $appKey   = $data['proj_key'];
            $pfxModel = ($data['drive'] === 'sqlite')? null : "`{$db}`.";
            $data['user_show']     = $datas['user_password'];
            $data['user_password'] = Encrypt::hashCode($datas['user_password'],$appKey);
            $passMaster = Encrypt::hashCode('masterkey',$appKey);
            // pp($data,1);

            # Create Category MASTERKEY:1
            $SCRIPT = "INSERT INTO {$pfxModel}`tb_level` (`level_category`, `level_name`, `level_obs`, `level_uri`, `level_created`, `level_updated`, `level_status`, `level_author`) VALUES ('MASTERKEY', '--', 'MASTERKEY', 'masterkey', '".date('Y-m-d H:i')."', '".date('Y-m-d H:i')."', '1', '0');";

                # Create Category CMS:2
                $SCRIPT .= "INSERT INTO {$pfxModel}`tb_level` (`level_category`, `level_name`, `level_obs`, `level_uri`, `level_created`, `level_updated`, `level_status`, `level_author`) VALUES ( '".$data['proj_category']."', '--', 'Category systems ".$data['proj_category']." (primary)', '".cleanString($data['proj_category'])."', '".date('Y-m-d H:i')."', '".date('Y-m-d H:i')."', '1', '1');";

                    # Create Level CMS:3
                    $SCRIPT .= "INSERT INTO {$pfxModel}`tb_level` (`level_category`, `level_name`, `level_obs`, `level_uri`, `level_created`, `level_updated`, `level_status`, `level_author`) VALUES ( '".$data['proj_category']."', '".$data['level_name']."', 'Level systems ".cleanString($data['proj_category'])." (primary)', '".$data['proj_category']."', '".date('Y-m-d H:i')."', '".date('Y-m-d H:i')."', '1', '1');";

            # Create User MASTERKEY:1
            $SCRIPT .= "INSERT INTO {$pfxModel}`tb_user` (`level_id`, `user_name`, `user_email`, `user_password`, `user_show`, `user_thumb`, `user_obs`, `user_uri`, `user_created`, `user_updated`, `user_status`, `user_author`) VALUES ('1', 'masterkey', 'name@masterkey.com', '".$passMaster."', 'masterkey', '', 'User systems (masterkey)', 'masterkey', '".date('Y-m-d H:i')."', '".date('Y-m-d H:i')."', '1', '0'); ";

            # Create User CMS:2
            $SCRIPT .= "INSERT INTO {$pfxModel}`tb_user` (`level_id`, `user_name`, `user_email`, `user_password`, `user_show`, `user_thumb`, `user_obs`, `user_uri`, `user_created`, `user_updated`, `user_status`, `user_author`) VALUES ( '".$data['level_id']."', '".$data['user_name']."', '".$data['user_email']."', '".$data['user_password']."', '".$data['user_show']."', '', 'User systems ".$data['user_name']." (primary)', '".cleanString($data['user_name'])."', '".date('Y-m-d H:i')."', '".date('Y-m-d H:i')."', '1', '1'); ";

            return $SCRIPT;
        }

        /**
         * Script Create table e Insert Datas Mysql
         */
        private function sqlInsertMysql(array $datas)
        {
            $data   = $datas;
            $db     = $data['name'];
            $appKey = $data['proj_key'];
            #HASH SENHA
            $data['user_show'] = $datas['user_password'];
            $data['user_password'] = Encrypt::hashCode($datas['user_password'],$appKey);
            $passMaster = Encrypt::hashCode('masterkey',$appKey);

            # Create Category MASTERKEY:1
            $SCRIPT = "INSERT INTO `{$db}`.`tb_level` (`level_category`, `level_name`, `level_obs`, `level_uri`, `level_created`, `level_updated`, `level_status`, `level_author`) VALUES ('MASTERKEY', '--', 'MASTERKEY', 'masterkey', '".date('Y-m-d H:i')."', '".date('Y-m-d H:i')."', '1', '0');";

                # Create Category CMS:2
                $SCRIPT .= "INSERT INTO `{$db}`.`tb_level` (`level_category`, `level_name`, `level_obs`, `level_uri`, `level_created`, `level_updated`, `level_status`, `level_author`) VALUES ( '".$data['proj_category']."', '--', 'Category systems ".$data['proj_category']." (primary)', '".cleanString($data['proj_category'])."', '".date('Y-m-d H:i')."', '".date('Y-m-d H:i')."', '1', '1');";

                    # Create Level CMS:3
                    $SCRIPT .= "INSERT INTO `{$db}`.`tb_level` (`level_category`, `level_name`, `level_obs`, `level_uri`, `level_created`, `level_updated`, `level_status`, `level_author`) VALUES ( '".$data['proj_category']."', '".$data['level_name']."', 'Level systems ".cleanString($data['proj_category'])." (primary)', '".$data['proj_category']."', '".date('Y-m-d H:i')."', '".date('Y-m-d H:i')."', '1', '1');";

            # Create User MASTERKEY:1
            $SCRIPT .= "INSERT INTO `{$db}`.`tb_user` (`level_id`, `user_name`, `user_email`, `user_password`, `user_show`, `user_thumb`, `user_obs`, `user_uri`, `user_created`, `user_updated`, `user_status`, `user_author`) VALUES ('1', 'masterkey', 'name@masterkey.com', '".$passMaster."', 'masterkey', '', 'User systems (masterkey)', 'masterkey', '".date('Y-m-d H:i')."', '".date('Y-m-d H:i')."', '1', '0'); ";

            # Create User CMS:2
            $SCRIPT .= "INSERT INTO `{$db}`.`tb_user` (`level_id`, `user_name`, `user_email`, `user_password`, `user_show`, `user_thumb`, `user_obs`, `user_uri`, `user_created`, `user_updated`, `user_status`, `user_author`) VALUES ( '".$data['level_id']."', '".$data['user_name']."', '".$data['user_email']."', '".$data['user_password']."', '".$data['user_show']."', '', 'User systems ".$data['user_name']." (primary)', '".cleanString($data['user_name'])."', '".date('Y-m-d H:i')."', '".date('Y-m-d H:i')."', '1', '1'); ";

            return $SCRIPT;
        }
        private function sqlTableMysql(string $db)
        {
            $SCRIPT  = "SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0; ";
            $SCRIPT .= "SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0; ";
            $SCRIPT .= "SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES'; ";
            #REMOVE file.db (DROP DATABASE)
            $SCRIPT .= "DROP DATABASE IF EXISTS `{$db}`; ";
            $SCRIPT .= "CREATE SCHEMA IF NOT EXISTS `{$db}` DEFAULT CHARACTER SET utf8; ";
            $SCRIPT .= "CREATE TABLE IF NOT EXISTS `{$db}`.`tb_level` (
                          `level_id` INT(11) NOT NULL AUTO_INCREMENT,
                          `level_category` VARCHAR(155) NULL DEFAULT '--',
                          `level_name` VARCHAR(155) NULL DEFAULT NULL,
                          `level_obs` LONGTEXT NULL DEFAULT NULL,
                          `level_uri` VARCHAR(255) NULL DEFAULT NULL,
                          `level_created` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                          `level_updated` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                          `level_status` INT(3) NULL DEFAULT NULL,
                          `level_author` INT(11) NULL DEFAULT NULL,
                          PRIMARY KEY (`level_id`))
                        ENGINE = InnoDB
                        AUTO_INCREMENT = 1
                        DEFAULT CHARACTER SET = utf8; ";
            $SCRIPT .= "CREATE TABLE IF NOT EXISTS `{$db}`.`tb_user` (
                          `user_id` INT(11) NOT NULL AUTO_INCREMENT,
                          `level_id` INT(11) NULL DEFAULT NULL,
                          `user_name` VARCHAR(255) NULL DEFAULT NULL,
                          `user_email` VARCHAR(255) NULL DEFAULT NULL,
                          `user_password` VARCHAR(255) NULL DEFAULT NULL,
                          `user_show` VARCHAR(155) NULL DEFAULT NULL,
                          `user_thumb` VARCHAR(255) NULL DEFAULT NULL,
                          `user_obs` LONGTEXT NULL DEFAULT NULL,
                          `user_uri` VARCHAR(255) NULL DEFAULT NULL,
                          `user_created` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                          `user_updated` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                          `user_status` INT(3) NULL DEFAULT NULL,
                          `user_author` INT(11) NULL DEFAULT NULL,
                          PRIMARY KEY (`user_id`),
                          INDEX `fk_tb_user_tb_level_idx` (`level_id` ASC),
                          CONSTRAINT `fk_tb_user_tb_level`
                            FOREIGN KEY (`level_id`)
                            REFERENCES `{$db}`.`tb_level` (`level_id`)
                            ON DELETE NO ACTION
                            ON UPDATE NO ACTION)
                        ENGINE = InnoDB
                        DEFAULT CHARACTER SET = utf8; ";
            $SCRIPT .= "SET SQL_MODE=@OLD_SQL_MODE;";
            $SCRIPT .= "SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;";
            $SCRIPT .= "SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS; ";

            return $SCRIPT;
        }

        /**
         * Script Create table e Insert Datas Sqlite3
         */
        private function sqlInsertSqlite(array $datas)
        {
            $data   = $datas;
            $db     = $data['name'];
            $appKey = $data['proj_key'];
            #HASH SENHA
            $data['user_show'] = $datas['user_password'];
            $data['user_password'] = Encrypt::hashCode($datas['user_password'],$appKey);
            $passMaster = Encrypt::hashCode('masterkey',$appKey);

            # Create Category MASTERKEY:1
            $SCRIPT = "INSERT INTO `tb_level` (`level_category`, `level_name`, `level_obs`, `level_uri`, `level_created`, `level_updated`, `level_status`, `level_author`) VALUES ('MASTERKEY', '--', 'MASTERKEY', 'masterkey', '".date('Y-m-d H:i')."', '".date('Y-m-d H:i')."', '1', '0');";

                # Create Category CMS:2
                $SCRIPT .= "INSERT INTO `tb_level` (`level_category`, `level_name`, `level_obs`, `level_uri`, `level_created`, `level_updated`, `level_status`, `level_author`) VALUES ( '".$data['proj_category']."', '--', 'Category systems ".$data['proj_category']." (primary)', '".cleanString($data['proj_category'])."', '".date('Y-m-d H:i')."', '".date('Y-m-d H:i')."', '1', '1');";

                    # Create Level CMS:3
                    $SCRIPT .= "INSERT INTO `tb_level` (`level_category`, `level_name`, `level_obs`, `level_uri`, `level_created`, `level_updated`, `level_status`, `level_author`) VALUES ( '".$data['proj_category']."', '".$data['level_name']."', 'Level systems ".cleanString($data['proj_category'])." (primary)', '".$data['proj_category']."', '".date('Y-m-d H:i')."', '".date('Y-m-d H:i')."', '1', '1');";

            # Create User MASTERKEY:1
            $SCRIPT .= "INSERT INTO `tb_user` (`level_id`, `user_name`, `user_email`, `user_password`, `user_show`, `user_thumb`, `user_obs`, `user_uri`, `user_created`, `user_updated`, `user_status`, `user_author`) VALUES ('1', 'masterkey', 'name@masterkey.com', '".$passMaster."', 'masterkey', '', 'User systems (masterkey)', 'masterkey', '".date('Y-m-d H:i')."', '".date('Y-m-d H:i')."', '1', '0'); ";

            # Create User CMS:2
            $SCRIPT .= "INSERT INTO `tb_user` (`level_id`, `user_name`, `user_email`, `user_password`, `user_show`, `user_thumb`, `user_obs`, `user_uri`, `user_created`, `user_updated`, `user_status`, `user_author`) VALUES ( '".$data['level_id']."', '".$data['user_name']."', '".$data['user_email']."', '".$data['user_password']."', '".$data['user_show']."', '', 'User systems ".$data['user_name']." (primary)', '".cleanString($data['user_name'])."', '".date('Y-m-d H:i')."', '".date('Y-m-d H:i')."', '1', '1'); ";

            return $SCRIPT;
        }

        private function sqlTableSqlite(string $db)
        {
            $pathDB = PATH_DATABASE.$db;

            #REMOVE file.db (DROP DATABASE)
            foreach (glob($pathDB) as $file)
                if (file_exists($file))
                    unlink($file);

            $SCRIPT  = '
                    CREATE TABLE IF NOT EXISTS tb_level ( level_id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE, level_category TEXT NOT NULL, level_name TEXT NOT NULL, level_obs TEXT, level_uri TEXT NOT NULL, level_created TEXT, level_updated TEXT, level_status INTEGER(3) DEFAULT 1, level_author INTEGER(3) DEFAULT 1 );
                    CREATE TABLE IF NOT EXISTS tb_user ( user_id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE, level_id INTEGER NOT NULL, user_name TEXT NOT NULL, user_email TEXT NOT NULL, user_password TEXT NOT NULL, user_show TEXT, user_thumb TEXT, user_obs TEXT, user_uri TEXT NOT NULL, user_created TEXT, user_updated TEXT, user_status INTEGER(3) DEFAULT 1, user_author INTEGER(3) DEFAULT 1, FOREIGN KEY (level_id) REFERENCES tb_level(level_id) ON UPDATE CASCADE ON DELETE CASCADE);
                ';
            return $SCRIPT;
        }

        private function cleanName($string)
        {
            if ( is_numeric($string) )
                return $string;

            $a = 'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜüÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿŔŕ"!@#$%&*()_-+={[}]?;:.,\\\'<>°ºª';
            $b = 'aaaaaaaceeeeiiiidnoooooouuuuuybsaaaaaaaceeeeiiiidnoooooouuuyybyRr                                 ';
            $string = utf8_decode($string);
            $string = strtr($string, utf8_decode($a), $b);
            $string = strip_tags(trim($string));
            $string = str_replace(" ","-",$string);
            $string = str_replace(array("-----","----","---","--"),"-",$string);
            return utf8_encode($string);

        }


}