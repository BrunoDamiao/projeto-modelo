<?php
namespace FwBD\Filter;

use FwBD\Filter\BaseFilter as BaseFilter;
use FwBD\DBConect\DBConect as Conn;
use FwBD\DI\Container;

class SystemFilter extends BaseFilter
{

    public function SetupIn($value='')
    {

        switch (DRIVE) {
            case 'mysql':
                return ( get_class( Conn::getCon() ) === 'PDOException' )? redirect('/setup'): '' ;
                break;
            case 'oracle':
                #...
                break;
            case 'postgres':
                #...
                break;
            case 'sqlite':
                $sql = \FwBD\Model\BaseModel::exec('SELECT * FROM sqlite_master WHERE 1');
                return (!$sql)? redirect('/setup') : '';
                break;
        }


    }

    public function SetupOut($value='')
    {
        // pp('SetupOut ............');
        // pp(get_class( Conn::getCon() ),1);

        switch (DRIVE) {
            case 'mysql':
                return ( get_class( Conn::getCon() ) === 'PDO' )? redirect('/auth/logout'): '' ;
                break;
            case 'oracle':
                #...
                break;
            case 'postgres':
                #...
                break;
            case 'sqlite':
                $sql = \FwBD\Model\BaseModel::exec('SELECT * FROM sqlite_master WHERE 1');
                return ($sql)? redirect('/auth/logout') : '';
                break;
        }

    }

    public function Auth($value='')
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

    public function Filter($value='')
    {

    }

}