<?php

/*Controlador del modulo entrada*/

class login_controller extends controller {

    public function execute() {
        header("Status: 401");
        if (($_SERVER["REQUEST_METHOD"] == "POST") and ($_POST["submit_button"] == "Entrar")) {
            if (strpos($_POST["username"], "'") !== false) {
                $this->view->add_message("Sorry, esta aplicación no soporta inyección SQL.");
            } else {
                $this->view->add_message("Login incorrecto");
            }
            /*En caso de fallo, recuerdo el username en el form*/
            $this->view->addContent([ 'username' =>  $_POST["username"]  ]);
        }
        /*el titulo del modulo*/
        $this->view->title          = 'Iniciar Sesión';
        /*la descripcion del modulo*/
        $this->view->description    = 'Iniciar Sesión en Guachi';
        /*el autor del modulo*/
        $this->view->author         = 'Máster Vitronic';
        /*El css principal*/
        $this->view->addCss('/css/themes/private/'.private_theme.'/style.css');
        /*el css del modulo*/
        $this->view->addCss('/css/themes/private/'.private_theme.'/login/login.css');
        /*seteo todos los parametros de la vista*/
        $this->view->set();
        /*La plantilla de la pagina*/
        $page   = $this->view->loadTemplate('page');
        /*En este caso el main va aqui*/
        $main   = $this->view->loadTemplate('login/main');
        /*Cargo las plantillas en la vista*/
        $this->view->load($page->render([
            /*esto carga los metadata*/
            'metatags'  => $this->view->meta,
            /*esto es el cuerpo html*/
            'main'      => $main->render($this->view->getContent()),
            /*este es el footer*/
            'footer'      => $this->view->loadTemplate('footer'),
            /*El/los js*/
            'js'        => $this->view->js
        ]));
        /*en este caso no se requiere cache*/
        $this->view->cache->cache_on = false;
        /*escupo el html ya renderizado*/
        $this->view->generate();
    }

}
