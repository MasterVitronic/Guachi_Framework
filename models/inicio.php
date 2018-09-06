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

class inicio_model extends model {

    public function show() {
        /*el titulo del modulo*/
        $this->view->title          = 'El titulo del modulo Inicio';
        /*la descripcion del modulo*/
        $this->view->description    = 'La descripcion del modulo Inicio';
        /*el autor del modulo*/
        $this->view->author         = 'Máster Vitronic';
        /*que esquema de tema usar 'admin' o 'public' */
        $this->view->useTheme('public');
        /*seteo todos los parametros de la vista*/
        $this->view->set();
        /*La plantilla de la pagina*/
        $page   = $this->view->tpl->loadTemplate($this->view->getTheme('page'));
        /*En este caso el main va aqui*/
        $main   = $this->view->tpl->loadTemplate($this->view->getTheme('inicio/main'));
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