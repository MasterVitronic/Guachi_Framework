<?php



class logout_controller extends controller {
    public function execute() {
        if ($this->auth->isLogged()) {
            if($this->auth->logOut()){
                header("Location: /".start_module, true, 307);
            }                
        } else {
            print 'Usted no esta logeado  <a href="/">Inicio</a> ';
        }
    }
}
