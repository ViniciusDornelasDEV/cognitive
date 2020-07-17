<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Usuario\Model;
use Application\Model\BaseTable;

class Recurso Extends BaseTable {

    public function getRecursosByTipoUsuario($where = array(), $params = false){
        return $this->getTableGateway()->select(function($select) use ($params, $where) {
            $select->join(array('ur' => 'tb_usuario_recurso'), 'ur.recurso = tb_recurso.id', array());
            $select->where($where);
        }); 
    }
}
