<?php

/*Modelo del modulo error 404*/

class notFound_model extends model {

    public function show() {
        header("Status: 404");
        /*el titulo del modulo*/
        $this->view->title          = 'ERROR 404';
        /*la descripcion del modulo*/
        $this->view->description    = 'La descripcion';
        /*el autor del modulo*/
        $this->view->author         = 'Máster Vitronic';
        /*seteo todos los parametros de la vista*/
        $this->view->set();
        /*La plantilla de la pagina*/
        $page   = $this->view->tpl->loadTemplate('/public/demo/page');
        /*En este caso el main va aqui*/
        $main   = $this->view->tpl->loadTemplate('/public/demo/404');
        /*Cargo las plantillas en la vista*/
        $this->view->load($page->render([
                'metatags'  => $this->view->meta,
                'main'      => $main->render(),
                'js'        => $this->view->js
            ]
        ));
        /*en este caso no se requiere cache*/
        $this->view->cache->cache_on = false;
        /*escupo el html ya renderizado*/
        $this->view->generate();
    }

}