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

class admin_model extends model {

    /*retorna toda la lista de usuarios*/
    public function get_users() {
        /*armo la consulta*/
        $query = "select * from users order by id_user desc";
        /*ejecuto la consulta*/
        if (($users = $this->db->execute($query)) === false) {
            return false;
        }
        /*retorno la data*/
        return $users;
    }

    /*retorna la informacion de un usuario*/
    public function get_user($id_user) {
        /*esto es cache*/
        static $users = array();
        if (isset($users[$id_user])) {
            /*si existe cache, la retorno*/
            return $users[$id_user];
        }
        /*hago el 'select * from users where id_user = %d' */
        if (($user = $this->db->entry("users", $id_user, 'id_user')) == false) {
            return false;
        }
        /*guardo esto en la cache*/
        $users[$id_user] = $user;
        /*retorno la data*/
        return $user;
    }

    /*crea un usuario nuevo*/
    public function create_user($user) {
        /*los campos a guardar*/
        $keys = array("username", "password", "fullname");
        /*creo la contraseña, vease la clase auth*/
        $user["password"] = $this->auth->hashPass($user["password"]);
        /*inicio la transaccion*/
        if ($this->db->query("begin") == false) {
            return false;
        }
        /*hago el insert*/
        if ($this->db->insert("users", $user, $keys) === false) {
            $this->db->query("rollback");
            return false;
        }
        /*finalmente hago el commit y retorno*/
        return $this->db->query("commit") != false;
    }

    /*actualiza un usuario*/
    public function update_user($user) {
        /*los campos a guardar*/
        $keys = array("username", "fullname");
        if ($user["password"] != "") {
            /*añado el campo pasword a los campos a guardar*/
            array_push($keys, "password");
            /*creo la contraseña, vease la clase auth*/
            $user["password"] = $this->auth->hashPass($user["password"]);
        }
        /*inicio la transaccion*/
        if ($this->db->query("begin") == false) {
            return false;
        }
        /*hago el updates*/
        if ($this->db->update("users", $user["id_user"], 'id_user', $user, $keys) === false) {
            /*algo fallo, rollback con esta transaccion*/
            $this->db->query("rollback");
            return false;
        }
        /*hago el commit y retorno*/
        return $this->db->query("commit") != false;
    }

    /*verifica que todo este bien*/
    public function save_oke($user) {
        $result = true; /*esto es true a menos que algo salga mal*/
        $validator   = new validator($this->view); /*inicializo la clase de validacion*/
        /*los campos a validar en este arreglo, creo que se explica solos*/
        $validations = [
            'username' => [
                'type'      => 'string',
                "required"  => true,
                "maxlen"    => 8
            ],
            'password' => [
                'type'      => 'string',
                "required"  => true,
                "minlen"    => 8
            ],
            'fullname' => [
                'message'   => 'Contraseña es Requerida',/*mensaje customizado*/
                'type'      => 'string',
                "required"  => true ,
                'minlen'    => 6
            ]
        ];

        /*esto es una edicion por que id_user existe*/
        if( isset($user["id_user"]) ){
           /*@TODO meter esto en el la clase validator*/
           /*verifico que este user no existe, a menos que sea self*/
           $exist = $this->db->exist('users', array_keys($validations), $user , 'id_user' , $user["id_user"] );
           if( $exist ){
               $this->view->add_message("El campo ".$exist." ya existe.");
               $result = false;
           }
        }

        /*hago todas las validaciones*/
        $check = $validator->execute($validations);
        if($check === false ){
            $result = false;
        }

        /*verifico las contraseñas*/
        if( $user["password"] != $user["password2"] ){
           $this->view->add_message("Las contraseñas no son iguales.");
           $result = false;            
        }
        return $result;
    }
}








