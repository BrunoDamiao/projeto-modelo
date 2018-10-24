<?php
namespace FwBD\Filter;

use FwBD\Filter\BaseFilter as BaseFilter;
use FwBD\DI\Container as Container;

class SystemFilter extends BaseFilter
{

    /**
     * $pagStatus - Define o estatus de conclusão da configuração do setup;
     * $pagStatus é inicializada com valor zero;
     * sts = 0 [pager setup](criar arquivo.json de configurar db);
     * sts = 1 [pager master](criar table e add datas);
     * sts = 2 [- pager setup/master](concluido a configuração do setup);
     */
    private $pagStatus = 0;

    public function __construct()
    {
        if ( $this->dbCheckedJson() ) { # Checa file.json
            $this->pagStatus = 1;

            if ( $this->dbCheckedData() )  # Checa user masterkey
                $this->pagStatus = 2;
        }
    }

    /**
     * Checa se .json foi criado (arquivo de configuração para conexão com db)
     * @return boolean: 0 - envia paar rota '/setup';
                        1 - segue sem fazer nada;
     */
    public function SetupDBConect()
    {
        pp('SetupDBConect ............ '.$this->pagStatus);

        /*if ($this->pagStatus < 2)
            redirect('/setup');*/
    }

    public function SetupTab($prmFilter)
    {
        pp('SetupTab ............ '.$this->pagStatus);

        /*if ($this->pagStatus == 2) {
            setMsgFlash('success', 'O setup do sistema já se encontra configurado com sucesso! :)');
            redirect('/auth');
        }*/
    }

    public function Auth($prmFilter='')
    {
        // pp('Auth ............',1);

        # autenticação do usuário
        if ( !Container::getSession('has', ['Auth']) ) {
            setMsgFlash('danger', "Acesso restrito! Área reservada somente para membros.
             <a href='/auth/create'> Assine agora mesmo. </a>");
            redirect('/auth');
        }

        # expira User JSON
        if ( Container::getSession('has', ['Auth']) && Json::has() ) {

            $dataSession = Container::getSession('get', ['Auth']);

            $atual = date('Y-m-d H:i:s');
            $final = $dataSession['session_timeEnd'];
                $dAtual = \DateTime::createFromFormat ('Y-m-d H:i:s', $atual);
                $dFinal = \DateTime::createFromFormat ('Y-m-d H:i:s', $final);

            if ( $dAtual > $dFinal ) {

                redirect('/auth/logout');

            }else{

                // echo '<br> Data de entrada menor que data de saida!';
                $dataSession['session_timeEnd'] = addDateTimeSession();
                Json::update($dataSession);
                Container::getSession('set', ['Auth', $dataSession]);

            }

        }

    }

    public function Filter($prmFilter='')
    {}


    /**
     * Methods HELPERS
     */

        /**
         * Checa se o arquivo de configuração do banco de dados foi criado;
         * aruivo do tipo file.json com informações do banco;
         * @return boolean [true: sim / false: não] criou file.json;
         */
        private function dbCheckedJson()
        {
            foreach (glob(PATH_DATABASE."*.json") as $file) {
                if (file_exists($file))
                    return true;
                return false;
            }
        }

        /**
         * Checa se as tabelas e usuário masterkey foi criado;
         * @return boolean [true: sim / false: não] criou usuário;
         */
        private function dbCheckedData()
        {
            $configDB = !empty(DB_CONFIG)? DB_CONFIG : getJsonDBConfig(PATH_STORAGE . 'database' .DS);
            // $pdo = \FwBD\DBConect\DBConect::connect( $configDB['drive'], $configDB );

            $pdo = new \PDO("mysql:host=localhost;", 'root', 'beca');

            // pp($pdo,1);
            if ( $configDB['drive'] === 'sqlite' ) {
                $stms = $pdo->query("SELECT name FROM sqlite_master WHERE type='table';");
                $rs = $stms->fetch(SQLITE3_ASSOC);
                if ( $rs['name'] ) # existe tabelas
                    return true;
                return false;
            }

            if ( $configDB['drive'] === 'mysql' ) {
                $stms = $pdo->query("SELECT * FROM tb_user WHERE 1");
                $rs = $stms->fetch();
                if ( $rs )
                    return true;
                else
                    return false;

            }
        }


}