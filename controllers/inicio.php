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

class inicio_controller extends controller {

    public function execute() {
        /*el titulo del modulo*/
        $this->view->title          = 'Guachi (Lightweight and very simple web development framework)';
        /*la descripcion del modulo*/
        $this->view->description    = 'Guachi (Lightweight and very simple web development framework)';
        /*el autor del modulo*/
        $this->view->author         = 'Máster Vitronic';
        /*El css principal*/
        $this->view->addCss('/css/themes/private/demo/mustard-ui.css');        
        /*seteo todos los parametros de la vista*/
        $this->view->set();
        /*La plantilla de la pagina*/
        $page   = $this->view->loadTemplate('page');
        /*En este caso el main va aqui*/
        $main   = $this->view->loadTemplate('inicio/main');
        /*Cargo las plantillas en la vista*/
        $this->view->load($page->render([
            /*esto carga los metadata*/
            'metatags'  => $this->view->meta,
            /*el menu header*/
            'menu'      => $this->view->loadTemplate('menu'),
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
