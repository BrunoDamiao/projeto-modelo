<?php
namespace FwBD\Request;

class Request implements iRequest
{

    public function all()
    {
        return $_REQUEST;
    }

    public function post()
    {
        return $_POST;
    }

    public function get()
    {
        return $_GET;
    }

    public function files($field)
    {
        return $_FILES[$field];
    }

    public function hasfiles($field)
    {
        return ( isset($_FILES[$field]) && $_FILES[$field]['name'] != '' );
    }
}