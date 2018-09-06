<?php
/*
            ____                  _     _
           / ___|_   _  __ _  ___| |__ (_)
          | |  _| | | |/ _` |/ __| '_ \| |
          | |_| | |_| | (_| | (__| | | | |
           \____|\__,_|\__,_|\___|_| |_|_|
Copyright (c) 2014  Díaz  Víctor  aka  (Máster Vitronic)
Copyright (c) 2018  Díaz  Víctor  aka  (Máster Vitronic)
<vitronic2@gmail.com>   <mastervitronic@vitronic.com.ve>
*/

require_once realpath(dirname( __DIR__ ) ) . '/lib/guachi.php';
require_once realpath(dirname( __DIR__ ) ) . '/lib/security.php';

/*Iniciamos todas las instancias*/
$router         = router::iniciar();
$auth           = auth::iniciar();
$view           = view::iniciar();

//print $router->url . PHP_EOL;

/*Se incluye el modelo*/
if (file_exists($file = DIR_MODELS.$router->module.".php")) {
    require_once($file);
}

/*Se incluye el controlador*/
if (file_exists($file = DIR_CONTROLLERS.$router->module.".php")) {
    require_once($file);
    $controller_class = str_replace("/", "_", $router->module)."_controller";
    if (class_exists($controller_class) == false) {
        print "Controller class '".$controller_class."' does not exist.\n";
    } else if (is_subclass_of($controller_class, "controller") == false) {
        print "Controller class '".$controller_class."' does not extend controller class.\n";
    } else {
        $_controller = new $controller_class($db, $auth, $router, $view);
        $_controller->execute();
        unset($_controller);
    }
}
