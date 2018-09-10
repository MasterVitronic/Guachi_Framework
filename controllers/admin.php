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


/*Controlador del modulo admin*/

class admin_controller extends controller {

    /*la plantilla del main*/
    private $main_tpl       = '';
    /*el titulo del modulo*/
    private $title          = 'El titulo del modulo admin';
    /*la descripcion del modulo*/
    private $description    = 'La descripcion del modulo admin';
    /*el autor del modulo*/
    private $author         = 'Máster Vitronic';

    private function set(){       
        /*En este caso de ejemplo no se usa CSS pero lo dejo aqui */
        //$this->view->addCss('/css/themes/admin/moscow/common/moscow.css');
        //$this->view->addCss('/css/themes/common/multi.min.css');   

        /*En este caso de ejemplo no se usa JS pero lo dejo aqui*/
        //$this->view->addJs('/js/themes/common/multi.min.js');
        //$this->view->addJs('/js/themes/common/multi.min.js');

        /*el titulo del modulo*/
        $this->view->title          = $this->title;
        /*la descripcion del modulo*/
        $this->view->description    = $this->description;
        /*el autor del modulo*/
        $this->view->author         = $this->author;
        /*seteo todos los parametros de la vista*/
        $this->view->set();
    }

    /*la funcion que escupe la pagina*/
    private function show() {
        /*La plantilla principal de la pagina*/
        $page   = $this->view->loadTemplate('page');
        /*Cargo las plantillas en la vista*/
        $this->view->load($page->render([
                /*esto carga los metadata*/
                'metatags'  => $this->view->meta,
                /*esto es el cuerpo html*/
                'main'      => $this->main_tpl->render( $this->view->getContent() ),
                /*en este caso js no se usa pero lo dejo aqui*/
                'js'        => $this->view->js
            ]
        ));
        $this->view->generate();
    }

    /*mostramos la vista general*/
    private function show_overview() {
        /**/
        $users = $this->model->get_users();
        if (($users === false)) {
            $this->view->add_message("Error la consultar la base de datos.");
        }
        /*edito el titulo*/
        $this->title = 'Mostrando la lista de usuarios disponibles';
        /*inicializo las variables sde la vista*/
        $this->set();
        /*seteo la cache en off*/
        $this->view->cache->cache_on = false;
        /*le meto el contenido de users a la plantilla main, para rellenar la tabla*/
        $this->view->addContent( [ 'users' => $users] );
        /*la plantilla main de la lista*/
        $this->main_tpl=$this->view->loadTemplate('admin/main');
        /*Muestro !*/
        $this->show();
    }

    /*mostramos el formulario*/
    private function show_form($user = []) {
        /*edito el titulo*/
        $this->title = 'El titulo del formulario';
        /*seteo la cache en off*/
        $this->view->cache->cache_on = false;
        /*inicializo las variables sde la vista*/
        $this->set();
        /*le meto el contenido de user a la plantilla main, para rellenar el formulario*/
        $this->view->addContent($user);
        /*la plantilla main del formulario*/
        $this->main_tpl=$this->view->loadTemplate('admin/form');
        /*Muestro !*/
        $this->show();        
    }
    
    /*La funcion execute es la primera que se ejecuta al iniciar el controlador*/
    public function execute() {
        /*esta variable recoje los parametros de la url,
         *es un array y el indice 0 indica el primer parametro*/
        $parameters = ($this->router->parameters) ? $this->router->parameters : false;

        if ($_SERVER["REQUEST_METHOD"] == "POST" and $_POST["submit_button"] == "Guardar") {
            /* Guardamos el usuario*/
            if ($this->model->save_oke($_POST) == false) {
                $this->show_form($_POST);
            } else if ( empty($_POST["id_user"] ) ) {
                /* Nuevo usuario , no hay id_user*/
                if ($this->model->create_user($_POST) === false) {
                    $this->view->add_message("Error en la base de datos durante la creación del usuario.");
                    $this->show_user_form($_POST);
                } else {
                    $this->show_overview();
                }
            } else {
                /* Actualizar usuario*/
                if ($this->model->update_user($_POST) === false) {
                    $this->view->add_message("Error en la base de datos durante la actualización del usuario.");
                    $this->show_form($_POST);
                }else {
                    header("Location: /admin", true, 301);
                    //header("Location: /admin");
                }
            }
        } else {
            if ($parameters[0] === "new") {
                /* Muestro el formulario de usuario por que el parametro es new
                 */
                $this->show_form();
            }  else if (valid_input($parameters[0], VALIDATE_NUMBERS, VALIDATE_NONEMPTY)) {
                /* el parametro es un numero , asi que se trata de una edicion
                 */
                if (($user = $this->model->get_user($parameters[0]) ) == false) {
                    /*el usuario no fue encontrado, añado un mensaje de error*/
                    $this->view->add_message("Usuario no encontrado.");
                    /*muestro la vista general*/
                    $this->show_overview();
                } else {
                    /*usuario encontrado, muestro el form de edicion
                     *con la informacion en $user para ser rellenados*/
                    $this->show_form($user);
                }
            } else {
                /* Muestro la lista con todos los usuarioss
                 */
                $this->show_overview();
            }
        }
    }
}

