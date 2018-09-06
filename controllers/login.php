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
        $this->view->title          = 'El titulo';
        /*la descripcion del modulo*/
        $this->view->description    = 'La descripcion';
        /*el autor del modulo*/
        $this->view->author         = 'Máster Vitronic';
        /*que tema usar admin o public */
        $this->view->useTheme('admin');
        /*seteo todos los parametros de la vista*/
        $this->view->set();
        /*La plantilla de la pagina*/
        $page   = $this->view->tpl->loadTemplate($this->view->getTheme('page'));
        /*En este caso el main va aqui*/
        $main   = $this->view->tpl->loadTemplate($this->view->getTheme('login/main'));
        /*Cargo las plantillas en la vista*/
        $this->view->load($page->render([
                'metatags'  => $this->view->meta,
                'main'      => $main->render($this->view->getContent()),
                'js'        => $this->view->js
            ]
        ));
        /*en este caso no se requiere cache*/
        $this->view->cache->cache_on = false;
        /*escupo el html ya renderizado*/
        $this->view->generate();
    }

}
