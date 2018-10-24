<?php
namespace FwBD\Filter;

use FwBD\Filter\BaseFilter as BaseFilter;
use FwBD\Json\Json as Json;
use FwBD\DI\Container as Container;

class SystemFilter extends BaseFilter
{

    public function Filter($prmFilter='')
    {
        if ( !$this->dbCheckedJson() )
            redirect('/setup');
    }

    public function SetupOut($prmFilter='')
    {
        if ( $this->dbCheckedJson() )
            redirect('/auth');
    }

    public function Auth($prmFilter='')
    {
        // pp('Auth ............');

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



}