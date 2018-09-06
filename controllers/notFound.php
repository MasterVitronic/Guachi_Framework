<?php

/*Controlador del modulo error 404*/

class notFound_controller extends controller {

    public function execute() {
        $this->model->show();
    }

}
