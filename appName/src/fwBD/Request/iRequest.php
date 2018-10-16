<?php
namespace FwBD\Request;

interface iRequest
{

    public function All();
    public function post();
    public function get();
    public function files($field);
    public function hasfiles($field);

}