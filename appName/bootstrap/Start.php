<?php
/**
 *  Carrega todos arquivos necessarios p/ funcionamento do AppBrFw;
 */

/*< init = inicialização do app >*/
require __DIR__ . "/../config/Session.php";
require __DIR__ . "/../config/Helpers.php";
require __DIR__ . "/../config/Init.php";
require __DIR__ . "/../config/DataBase.php";
require __DIR__ . "/../config/Mail.php";

/*foreach (glob( __DIR__ . "/../config/*.php") as $arquivo) {
    require $arquivo;
}*/

/*< filters = gestão de filtros/middleware >*/
require __DIR__ . "/../app/Filters.php";

/*< autoload = set file de autoload >*/
require __DIR__ . "/../vendor/autoload.php";

/*< routers = gestão das rotas >*/
require __DIR__ . "/../app/Routers.php";