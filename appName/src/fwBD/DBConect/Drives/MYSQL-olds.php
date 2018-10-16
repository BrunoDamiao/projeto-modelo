<?php
namespace FwBD\DBConect\Drives;

use PDO;
use PDOException;


class MYSQL implements iDB
{

    public function getConBD()
    {
        
        $host       = CONFIG_DB['HOST'];
        $db         = CONFIG_DB['DBS'];
        $user       = CONFIG_DB['USER'];
        $pass       = CONFIG_DB['PASS'];
        $charset    = CONFIG_DB['CHARSET'];
        $collation  = CONFIG_DB['COLLATION'];

        try {
            
            $pdo = new PDO("mysql:host=$host;dbname=$db;charset=$charset", $user, $pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::MYSQL_ATTR_INIT_COMMAND, "SET NAMES '$charset' COLLATE '$collation'");
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);

            return $pdo;

        } catch (PDOException $e) {

            // return false;
            // die('Error Connect DB-SQLITE: ').$e->getMessage();
            return $e;

            /*
            // Self::createDbSystems();

            $mesg1 = "Erro ao conectar com banco de dados! ".$e->getMessage();
            $mesg2 = "Criando base de dados {$db}, com a usuário e senha padrão! aperte a tecla <strong>F5</strong> para avançar";

            $this->outMsg($mesg1);
            $this->outMsg($mesg2);
            exit();*/

        }

    }

    public static function createDbSystems()
    {

        $host       = CONFIG_DB['HOST'];
        $db         = CONFIG_DB['DBS'];
        $user       = CONFIG_DB['USER'];
        $pass       = CONFIG_DB['PASS'];
        $charset    = CONFIG_DB['CHARSET'];
        $collation  = CONFIG_DB['COLLATION'];


        try {

            $obj = new PDO("mysql:host=$host;dbname=$db;charset=$charset", $user, $pass);
            unset($obj);

            return 1;
            
        } catch (PDOException $e) {
          
          # CREATE DATABASE (appwebdev)
          $SCRIPT  = "SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0; ";
            $SCRIPT .= "SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0; ";
            $SCRIPT .= "SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES'; ";

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
                        DEFAULT CHARACTER SET = utf8; 

                        CREATE TABLE IF NOT EXISTS `{$db}`.`tb_user` (
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

          # INSERT DATAS (tb_level tb_user)
          $SCRIPT .= "INSERT INTO `{$db}`.`tb_level` (`level_category`, `level_name`, `level_obs`, `level_uri`, `level_created`, `level_updated`, `level_status`, `level_author`) VALUES ('MASTERKEY', '--', 'MASTERKEY', 'masterkey', '".date('Y-m-d H:i')."', '".date('Y-m-d H:i')."', '1', '0');";

          
          # $password = Encrypt::hashCode('masterkey');
          $dataUser     = 'admin';
          $dataEmail    = 'masterkey@mk.com';
          $dataPassword = '76861ae9ac3aa8a79a21e392d771e2a6b46c7a15a6aae32035c6d5c8547b7bf8e06dc3874d04a1dad703f3061de039c3d053a61db7d44c3636c9d96d5c433c59';

          $SCRIPT .= "INSERT INTO `{$db}`.`tb_user` (`level_id`, `user_name`, `user_email`, `user_password`, `user_show`, `user_thumb`, `user_obs`, `user_uri`, `user_created`, `user_updated`, `user_status`, `user_author`) VALUES ('1', '".$dataUser."', '".$dataEmail."', '".$dataPassword."', 'masterkey', '', 'obs masterkey', 'masterkey', '".date('Y-m-d H:i')."', '".date('Y-m-d H:i')."', '1', '0');";
              
          $pdo = new PDO("mysql:host=$host;", $user, $pass);
          
          return $pdo->exec($SCRIPT);

        }

    }


    private function outMsg($msg)
    {

      echo '<div class="alert alert-info hidden-time alert-dismissable fade in" role="alert">
          <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>';
          echo $msg;
      echo '</div>';

    }

}

