<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Usuario\Model;
use Application\Model\BaseTable;

class UsuarioRecurso Extends BaseTable {

    public function getRecursosByTipoRecurso($params){
        return $this->getTableGateway()->select(function($select) use ($params) {
                    $select->join(
                        array('r' => 'tb_recurso'), 
                        'r.id = recurso', 
                        array('nome', 'descricao'));

                    $select->join(
                        array('m' => 'tb_recurso_modulo'), 
                        'm.id = r.modulo', 
                        array('nome_modulo'));

                    $select->where($params);

                    $select->order('nome_modulo, r.nome');


                }); 
    }
}
