define('LOG_FILENAME', $_SERVER['DOCUMENT_ROOT'] . '/log.txt');
AddMessage2Log(print_r(json_decode($_POST), true), "main");
