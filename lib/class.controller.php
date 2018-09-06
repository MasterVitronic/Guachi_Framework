<?php

/*la case del controlador*/

abstract class controller {
    protected $model = null;
    protected $db = null;
    protected $auth = null;
    protected $router = null;
    protected $view = null;

    /* Constructor
     *
     * INPUT:  object db, object auth, object page, object view
     * OUTPUT: -
     * ERROR:  -
     */
    public function __construct($db, $auth, $router, $view) {
        $this->db = $db;
        $this->auth = $auth;
        $this->router = $router;
        $this->view = $view;

        /* Load model
         */
        $model_class = str_replace("/", "_", $router->module)."_model";
        if (class_exists($model_class)) {
            if (is_subclass_of($model_class, "model") == false) {
                print "Model class '".$model_class."' does not extend class 'model'.\n";
            } else {
                $this->model = new $model_class($db, $auth, $router, $view);
            }
        }
    }

    /* Default execute function
     *
     * INPUT:  -
     * OUTPUT: -
     * ERROR:  -
     */
    public function execute() {
        if ($this->page->ajax_request == false) {
            print "Page controller has no execute() function.\n";
        }
    }
}

