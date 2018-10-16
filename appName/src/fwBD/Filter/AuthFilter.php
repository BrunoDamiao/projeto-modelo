<?php
namespace FwBD\Filter;

use FwBD\Filter\BaseFilter as BaseFilter;
use FwBD\DI\Container as Container;
use FwBD\Json\Json;

class AuthFilter extends BaseFilter
{
    public function Filter($value='')
    {
        # Autenticação do usuário
        # 1. verifica se sessao do user existe
        # 2. verifica se sessao do user existe

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

}