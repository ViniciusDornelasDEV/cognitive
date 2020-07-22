<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Invoice\Model;
use Application\Model\BaseTable;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Predicate\Expression;

class Invoice Extends BaseTable {

    public function getInvoicesByParams($params){
        return $this->getTableGateway()->select(function($select) use ($params) {
          if(isset($params['data_inicio']) && !empty($params['data_inicio'])){
              $select->where->between('data_vencimento', $params['data_inicio'], $params['data_fim']);
          }

          if(isset($params['cliente']) && !empty($params['cliente'])){
            $select->where(array('cliente' => $params['cliente']));
          }

          $select->order('data_vencimento DESC');
        }); 
    }

}
