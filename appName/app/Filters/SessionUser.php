<?php

namespace App\Filters;

use FwBD\Filter\BaseFilter;
use FwBD\Controller\BaseController;
use FwBD\DI\Container as Container;

/**
*
*/
class SessionUser extends BaseFilter
{

    public function Filter($value='')
    {

        if ( Container::getSession('has', ['Auth']) ) {
	
	        $auth = (object) Container::getSession('get', ['Auth']);

            /*switch ( $auth->session_category ) {
                case 'Masterkey':
                    setMsgFlash('danger', "Acesso restrito! Você não tem privilégios para acessar esta área.");
                    redirect('/admin');
                    break;

                case 'EAD':
                    setMsgFlash('danger', "Acesso restrito! Você não tem privilégios para acessar esta área.");
                    redirect('/admin');
                    break;

                case 'BLOG':
                    setMsgFlash('danger', "Acesso restrito! Você não tem privilégios para acessar esta área.");
                    redirect('/admin');
                    break;

                default:
                    setMsgFlash('danger', "Acesso restrito! Você não tem privilégios para acessar esta área.");
                    redirect('/admin');
                    break;

            }*/
        	
        }

    }


}