<?php

/*Modelo del modulo Inicio*/

class admin_article_model extends model {

    /**
     * Método parser
     *
     *
     * Si encuenta codigo lo colorea con geshi
     */
    public function parser($s) {
        $offset = 0;
        $result = '';
        // Separo el codigo por un lado y el texto por el otro, al codigo lo formateo y luego lo junto
        $n = preg_match_all('|((\r?\n```+)\s*([a-zA-Z0-9_-]*)\r?\n)(.*?)\2\r?\n|s', $s, $matches, PREG_OFFSET_CAPTURE);
        for($i = 0; $i < $n; $i++) {
            $md = substr($s, $offset, $matches[4][$i][1] - $offset - strlen($matches[1][$i][0]));
            $result .= $md ;
            $code = html_entity_decode(trim($matches[4][$i][0]));
            $language = strtolower($matches[3][$i][0]);
            $lang = ($language) ? $language : 'text' ;
            $geshi = new \lib\GeSHi\GeSHi($code,  $lang );
            $geshi->enable_classes();
            $geshi->set_link_target('_blank');          
            $geshi->set_case_keywords(GESHI_CAPS_LOWER);
            $result .= $geshi->parse_code();
            $offset = $matches[4][$i][1] + strlen($matches[4][$i][0]) + strlen($matches[2][$i][0]);
        }
        $result .= substr($s, $offset) ; 
        $md2html = new \lib\Parsedown\ParsedownExtra();
        return $md2html->text($result);
    }

    /**
     * Método getFormatMd
     *
     * Retorna la opcion de formatear o no el  SRC MD segun sea el caso
     */
    public function getFormatMd() {
        $request = $this->router->parameters[0];
        $si = '';
        $no = '';
        if ( $request == 'new' ) {
            $si = 'selected';
        }else{
            $no = 'selected';
        }
        $data=[
             ['normaliceMd' => '<option '.$si.' value="t">SI</option>'],
             ['normaliceMd' => '<option '.$no.' value="f">NO</option>']
        ];
        return $data;
    }

    /**
     * Método getTags
     *
     * Retorna las etiquetas 
     */
    private function getTags($newPost = false) {
        $id_post = ($this->request->id) ? $this->request->id : '0';
        $query = "select tags.id_tag,tag,id_post from tags "
                ."left outer join posts_tagged on (posts_tagged.id_tag=tags.id_tag) "
                ."where 1=1 or id_post='$id_post' "
                ."group by tags.id_tag order by tags.id_tag desc ";
        $results = $this->cbd->get_results($this->sql);
        if ($results) {
            foreach ($results as $campo => $valor) {
                $selected = ($valor->id_post == $id_post) ? 'selected' : '';
                if(is_array($newPost)){
                    $selected = in_array($valor->id_tag,$newPost) ? 'selected' : '';
                }
                $data[]=[
                     'tag' => '<option '.$selected.' value="'.$valor->id_tag.'">'.$valor->tag.'</option>'
                ];
            }
            return $data;
        }
        return false;        
    }




		//public function save_oke($weblog) {
			//$result = true;

			//if (trim($weblog["title"]) == "") {
				//$this->view->add_message("Title can't be empty.");
				//$result = false;
			//}

			//if (trim($weblog["content"]) == "") {
				//$this->view->add_message("Weblog content can't be empty.");
				//$result = false;
			//}

			//return $result;
		//}
        
		//public function get($offset, $count) {
			//$query = "select w.id, w.user_id, w.title, w.visible, UNIX_TIMESTAMP(w.timestamp) as timestamp, u.fullname as author, ".
			         //"(select count(*) from weblog_comments where weblog_id=w.id) as comments ".
			         //"from weblogs w, users u where w.user_id=u.id";
			//$args = array();

			//if ($this->user->is_admin == false) {
				//$query .= " and w.user_id=%d";
				//array_push($args, $this->user->id);
			//}

			//$query .= " order by timestamp desc limit %d,%d";
			//array_push($args, $offset, $count);

			//return $this->db->execute($query, $args);
		//}


		//public function create($weblog) {
			//$keys = array("id", "user_id", "title", "content", "timestamp", "visible");

			//$weblog["id"] = null;
			//$weblog["user_id"] = $this->user->id;
			//$weblog["timestamp"] = null;
			//$weblog["visible"] = is_true($weblog["visible"]) ? YES : NO;

			//if ($this->db->query("begin") === false) {
				//return false;
			//} else if ($this->db->insert("weblogs", $weblog, $keys) === false) {
				//$this->db->query("rollback");
				//return false;
			//} else if ($this->handle_tags($this->db->last_insert_id, $weblog) == false) {
				//$this->db->query("rollback");
				//return false;
			//}

			//return $this->db->query("commit") != false;
		//}

		//public function update($weblog) {
			//if ($this->get_weblog($weblog["id"]) == false) {
				//return false;
			//}

			//$keys = array("title", "content", "visible");

			//$weblog["visible"] = is_true($weblog["visible"]) ? YES : NO;

			//if ($this->db->query("begin") === false) {
				//return false;
			//} else if ($this->db->update("weblogs", $weblog["id"], $weblog, $keys) === false) {
				//$this->db->query("rollback");
				//return false;
			//} else if ($this->handle_tags($weblog["id"], $weblog) == false) {
				//$this->db->query("rollback");
				//return false;
			//}
			//return $this->db->query("commit") != false;
		//}

		//public function delete($weblog_id) {
			//if ($this->get_weblog($weblog_id) == false) {
				//return false;
			//}

			//if ($this->db->query("begin") == false) {
				//return false;
			//} else if ($this->db->query("delete from weblog_comments where weblog_id=%d", $weblog_id) == false) {
				//$this->db->query("rollback");
				//return false;
			//} else if ($this->db->query("delete from weblog_tagged where weblog_id=%d", $weblog_id) == false) {
				//$this->db->query("rollback");
				//return false;
			//} else if ($this->db->query("delete from weblogs where id=%d", $weblog_id) == false) {
				//$this->db->query("rollback");
				//return false;
			//} else if ($this->delete_unused_tags() == false) {
				//$this->db->query("rollback");
				//return false;
			//}

			//return $this->db->query("commit") != false;
		//}

}