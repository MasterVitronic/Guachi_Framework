<?php

/*la clase del modelo*/

abstract class model {

    protected $db = null;
    protected $auth = null;
    protected $router = null;
    protected $view = null;

    /* Constructor
     *
     * INPUT: object database, object settings, object user, object page, object view[, object language]
     * OUTPUT: -
     * ERROR:  -
     */
    public function __construct($db, $auth, $router, $view) {
        $this->db = $db;
        $this->auth = $auth;
        $this->router = $router;
        $this->view = $view;
    }

    /* Borrow function from other model
     *
     * INPUT:  string module name
     * OUTPUT: object model
     * ERROR:  null
     */
    protected function borrow($module) {
        if (file_exists($file = "../models/".$module.".php") == false) {
            header("Content-Type: text/plain");
            printf("Can't borrow model '%s'.\n", $module);
            exit();
        }

        require_once($file);

        $model_class = str_replace("/", "_", $module)."_model";
        if (class_exists($model_class) == false) {
            return null;
        } else if (is_subclass_of($model_class, "model") == false) {
            return null;
        }

        return new $model_class($this->db, $this->auth, $this->router, $this->view);
    }
}
