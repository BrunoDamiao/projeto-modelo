<?php
session_name('APP_NAME');
session_start();
ini_set('session.save_path', 'tmp/');

/*ini_set('session.save_handler', 'files');
session_save_path('/tmp/'); */

define("EXPIRE_SESSION_AUTH", (60*30));
