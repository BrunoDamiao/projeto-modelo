<?php
namespace App\Controllers;

use FwBD\Controller\BaseController;
use FwBD\DI\Container;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

class HomeController extends BaseController
{

    private $totalPage = 3; #APP_PAGINATOR;
    private $model;

    public function __construct($params)
    {
        parent::__construct($params);
        // Container::setFilter(['createDB']);
        // Container::setFilter(['SetupSystem']);
        // Container::setFilter(['middleware2']);
        Container::setTemplateView('site.templates.template');
    }


    /**
     * Methods GET
     */

        public function getIndex()
        {
            $title = 'Home';
            Container::getView('site.home', compact('title'));
        }

        public function getAbout()
        {
            $title = 'About';
            Container::getView('site.about', compact('title'));
        }

        public function getContact()
        {
            $title = 'Contact';
            Container::getView('site.Contact', compact('title'));
        }

        /*# Page setup systems #
        public function getSettings()
        {
            $title = 'Settings Systems';
            Container::getView('Settings', compact('title'));
        }*/



    /**
     * Methods POST
     */






    /**
     * Methods HELPER
     */



}