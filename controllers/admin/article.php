<?php

/*Controlador del modulo Inicio*/

class admin_article_controller extends controller {
    private $content_view   = '';
    private $title          = 'El titulo';
    private $description    = 'La descripcion';
    private $author         = 'Master Vitronic';

    private function set(){
        /*añado los css*/
        $this->view->addCss('/css/themes/admin/moscow/common/moscow.css');
        $this->view->addCss('/css/themes/common/multi.min.css');
        $this->view->addCss('/css/themes/common/simplemde.min.css');
        $this->view->addCss('/css/themes/common/font-awesome.css');
        $this->view->addCss('/css/themes/admin/moscow/common/common.css');
        $this->view->addCss('/css/themes/admin/moscow/article/main.css');       
        /*añado los js*/
        $this->view->addJs('/js/themes/common/multi.min.js');
        $this->view->addJs('/js/themes/common/simplemde.min.js');
        $this->view->addJs('/js/themes/admin/moscow/article/main.js');
        /*el content de los metatgs*/
        $this->view->setTitle($this->title);
        $this->view->setDescription($this->description);
        $this->view->setAuthor($this->author);
        /*que tema usar admin o public */
        $this->view->useTheme('admin');
        /*seteo todos los parametros de la vista*/
        $this->view->set();
    }

    private function show() {
        /*La plantilla de la pagina*/
        $page   = $this->view->tpl->loadTemplate($this->view->getTheme('page'));
        /*En este caso el main va aqui*/
        $main   = $this->view->tpl->loadTemplate($this->view->getTheme('article/main'));
        /*el menu*/
        $menu   = $this->view->tpl->loadTemplate($this->view->getTheme('menu'));
        /*Finalmente renderizo la pagina*/
        $this->view->load($page->render([
                    'metatags'  => $this->view->meta,
                    'main'      => $main->render([
                        'menu'     => $this->view->tpl->loadTemplate( $this->view->getTheme('menu') ),
                        'content'  => $this->content_view->render( $this->view->getContent() ),
                        'footer'   => $this->view->tpl->loadTemplate( $this->view->getTheme('footer') )
                    ]),
                    'js'        => $this->view->js
                ]
        ));
        /*escupo el html ya renderizado*/
        $this->view->generate();
    }

    /*mostramos la vista general*/
    private function show_overview() {
        $this->title = 'El titulo de la lista';
        /*seteo la cache en off*/
        $this->view->cache->cache_on = false;
        $this->set();
        /*el content*/
        $this->content_view=$this->view->tpl->loadTemplate($this->view->getTheme('article/list'));
        //print_r();
        $this->show();
    }

    /*mostramos la vista general*/
    private function show_not_found() {
        $this->title = 'No encontrado';
        /*seteo la cache en off*/
        $this->view->cache->cache_on = false;
        $this->set();  
        /*el content*/
        $this->content_view=$this->view->tpl->loadTemplate($this->view->getTheme('404'));
        $this->show();
    }

    /*mostramos el formulario*/
    private function show_form() {
        $this->title = 'El titulo del formulario';
        /*seteo la cache en off*/
        $this->view->cache->cache_on = false;
        $this->set();  
        /*el content*/
        $this->content_view=$this->view->tpl->loadTemplate($this->view->getTheme('article/form'));
        $this->show();        
    }

    /*ejecutamos*/
    public function execute() {
        //$this->model->show();
        //if ($_SERVER["REQUEST_METHOD"] == "POST") {
            //if ($_POST["submit_button"] == "Save weblog") {
                ///* Guardar
                 //*/
                //if ($this->model->save_oke($_POST) == false) {
                    //$this->show_form($_POST);
                //} else if (isset($_POST["id"]) == false) {
                    ///* Crear nuevo
                     //*/
                    //if ($this->model->create($_POST) == false) {
                        //$this->view->add_message("Database error while creating weblog.");
                        //$this->show_form($_POST);
                    //} else {
                        //$this->user->log_action("weblog %d created", $this->db->last_insert_id);
                        //$this->show_overview();
                    //}
                //} else {
                    ///* Actualizar
                     //*/
                    //if ($this->model->update($_POST) == false) {
                        //$this->view->add_message("Database error while updating weblog.");
                        //$this->show_form($_POST);
                    //} else {
                        //$this->user->log_action("weblog %d updated", $_POST["id"]);
                        //$this->show_overview();
                    //}
                //}
            //} else if ($_POST["submit_button"] == "Delete weblog") {
                ///* Borrar
                 //*/
                //if ($this->model->delete($_POST["id"]) == false) {
                    //$this->view->add_tag("result", "Error while deleting weblog.");
                //} else {
                    //$this->user->log_action("weblog %d deleted", $_POST["id"]);
                    //$this->show_overview();
                //}
            //} else {
                //$this->show_overview();
            //}
        //} else if (valid_input($this->page->parameters[0], VALIDATE_NUMBERS, VALIDATE_NONEMPTY)) {
            ///* Mostrar
             //*/
            //if (($weblog = $this->model->get($this->page->parameters[0])) == false) {
                //$this->view->add_tag("result", "Weblog not found.");
            //} else {
                //$this->show_form($weblog);
            //}
        //} else if ($this->page->parameters[0] == "new") {
            ///* Nuevo form
             //*/
            //$this->show_form($weblog);
        //} else {
            ///* Mostrar vista general
             //*/
            //$this->show_overview();
        //}


        if ( isset($this->router->parameters[0]) and  $this->router->parameters[0] != "new") {
            $this->show_not_found();
        }else if ( isset($this->router->parameters[0]) and $this->router->parameters[0] == "new" ) {

            /*acciones al guardar*/
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $validator   = new validator($this->view);
                $checks      = $validator->execute([
                    'title'       => [ 'type' =>  'email'  , "required" => true ],
                    'articleBody' => [ 'type' =>  'string' , "required" => true ,  'minlen' => 5 ]
                ]);
                if (!$checks) {
                    $this->view->addContent([
                        'articleBody' => $_POST['articleBody'],
                        'title'       => $_POST['title'],
                        'normaliceMd' => $this->model->getFormatMd()
                    
                    ]);
                }else{
                   
                }
            }
            $this->view->addContent([
                'normaliceMd' => $this->model->getFormatMd()
            
            ]);
            $this->show_form();
        }else{
            $this->view->addContent([ 'list' => 'Holas tuyos' ]);
            $this->show_overview();
        }

        
    }

        
}
