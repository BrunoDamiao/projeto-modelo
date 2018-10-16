<?php
namespace FwBD\View;

interface iView
{
    public function setTitle($title);
    public function getTitle();

    public function setData($data);
    public function getData();

    public static function setTempViews($tempViews);
    public function setPathViews($pathViews);

    public function render();
    public function renderTemplate();

    public function run();
}