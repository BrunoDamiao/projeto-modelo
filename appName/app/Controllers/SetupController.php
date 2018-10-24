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

        Container::setFilter(['SetupOut']);
        Container::setTemplateView('setup.templates.template');
        $this->config = DB_CONNECTIONS; //(DB_CONFIG)?? $connections;
    }


    /**
     * Methods GET
     */

    public function getIndex()
    {
        $title = 'Settings DataBase';
        $data = $this->config;

        Container::getView('setup.settings', compact('title','data'));
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
            // pp($data);

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
                'user_password' => 'requerid | min:2 | max:255'
            ];
                $validate->validateData($rules, $data);
                if ($validate->getStatus()) {
                    setDataInput($data);
                    setMsgFlash('warning', $validate->getMessages());
                    return redirect("/settings");
                }
            // pp($data);

            # Monta string path do arquivo.json (file conexão com db)
            if ( $data['drive'] === 'sqlite' ) {
                $jshost       = substr_replace($rs['sqlite_host'], '', strlen(PATH_DATABASE));
                $jsname       = explode('.', $rs['sqlite_name']);
                $data['host'] = $jshost . $jsname[0] . '.db';
                $path         = $jshost . $jsname[0] . '.json';
            }else{
                $path = PATH_DATABASE . $rs['mysql_name'] . '.json';
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
                $dt['name']     = $rs['sqlite_name'];
                $dt['user']     = $rs['sqlite_attr1'];
                $dt['password'] = $rs['sqlite_attr2'];
                $dt['charset']  = $rs['sqlite_attr3'];
                $dt['collation']= $rs['sqlite_attr4'];
            }elseif ( $rs['DBDrive'] === 'mysql-tab' ) {
                $dt['drive']    = 'mysql';
                $dt['host']     = $rs['mysql_host'];
                $dt['name']     = $rs['mysql_name'];
                $dt['user']     = $rs['mysql_user'];
                $dt['password'] = $rs['mysql_password'];
                $dt['charset']  = $rs['mysql_charset'];
                $dt['collation']= $rs['mysql_collation'];
            }

            $dt['user_name'] = $rs['user_name'];
            $dt['user_email'] = $rs['user_email'];
            $dt['user_password'] = $rs['user_password'];

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
            if ( $config['drive'] === 'sqlite' ) {
                $script  = $this->sqlTableSqlite($config['name']);
                $script .= $this->sqlInsertSqlite($config);
            }
            if ( $config['drive'] === 'mysql' ) {
                $script  = $this->sqlTableMysql($config['name']);
                $script .= $this->sqlInsertMysql($config);
            }

            try {
                $pdo = $this->getConPDO($config);
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
        private function sqlInsertMysql(array $datas)
        {
            $data   = $datas;
            $db     = $data['name'];
            #HASH SENHA
            $data['user_show'] = $datas['user_password'];
            $data['user_password'] = \FwBD\Encrypt\Encrypt::hashCode($datas['user_password']);

            // $SCRIPT = $this->sqlTableMysql($db);

            $SCRIPT = "INSERT INTO `{$db}`.`tb_level` (`level_category`, `level_name`, `level_obs`, `level_uri`, `level_created`, `level_updated`, `level_status`, `level_author`) VALUES ('MASTERKEY', '--', 'MASTERKEY', 'masterkey', '".date('Y-m-d H:i')."', '".date('Y-m-d H:i')."', '1', '0');";

            $SCRIPT .= "INSERT INTO `{$db}`.`tb_user` (`level_id`, `user_name`, `user_email`, `user_password`, `user_show`, `user_thumb`, `user_obs`, `user_uri`, `user_created`, `user_updated`, `user_status`, `user_author`) VALUES ('1', '".$data['user_name']."', '".$data['user_email']."', '".$data['user_password']."', 'masterkey', '', 'obs masterkey', 'masterkey', '".date('Y-m-d H:i')."', '".date('Y-m-d H:i')."', '1', '0'); ";

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
            #HASH SENHA
            $data['user_show'] = $datas['user_password'];
            $data['user_password'] = \FwBD\Encrypt\Encrypt::hashCode($datas['user_password']);

            $SCRIPT = "INSERT INTO `tb_level` (`level_category`, `level_name`, `level_obs`, `level_uri`, `level_created`, `level_updated`, `level_status`, `level_author`) VALUES ('MASTERKEY', '--', 'MASTERKEY', 'masterkey', '".date('Y-m-d H:i')."', '".date('Y-m-d H:i')."', '1', '0');";

            $SCRIPT .= "INSERT INTO `tb_user` (`level_id`, `user_name`, `user_email`, `user_password`, `user_show`, `user_thumb`, `user_obs`, `user_uri`, `user_created`, `user_updated`, `user_status`, `user_author`) VALUES ('1', '".$data['user_name']."', '".$data['user_email']."', '".$data['user_password']."', 'masterkey', '', 'obs masterkey', 'masterkey', '".date('Y-m-d H:i')."', '".date('Y-m-d H:i')."', '1', '0'); ";

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


}