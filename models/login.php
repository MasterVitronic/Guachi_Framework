<?php



/**
 * Porque el archivo del modelo se carga antes de que se genere cualquier salida,
 * se utiliza para manejar el envío de los datos de inicio de sesión.
*/

$login_successful = false;
if ( ($_SERVER["REQUEST_METHOD"] == "POST") and ($_POST["submit_button"] == "Entrar") ) {
    if( $auth->logIn($_POST['username'], $_POST['password']) ){
        $login_successful = true;
    }
}
/* Acciones previas al inicio de sesión
 */
if ($login_successful) {
    /* Cargar página solicitada
     */
    if (($next_page = ltrim($router->url, "/")) == "") {
        $next_page = start_module;
    }
    $router->select_module($next_page);
    if ($router->module != LOGIN_MODULE) {
        if (file_exists($file = DIR_MODELS.$router->module.".php")) {
            include($file);
            header("Location: ".$router->url, true, 301);
        }
    }
}
