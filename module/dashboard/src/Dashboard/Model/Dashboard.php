<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Dashboard\Model;
use Application\Model\BaseTable;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Predicate\Expression;

class Dashboard Extends BaseTable {

    public function getDashboardsByParams($params){
        return $this->getTableGateway()->select(function($select) use ($params) {
          $select->join(
              array('dc' => 'tb_dashboard_categoria'),
              'dc.id = categoria',
              array('nome_categoria' => 'nome', 'icone_categoria' => 'icone'),
              'LEFT'
          );

          if(isset($params['cliente']) && !empty($params['cliente'])){
            $select->where(array('tb_dashboard.cliente' => $params['cliente']));
          }


          if(isset($params['categoria']) && !empty($params['categoria'])){
            $select->where(array('categoria' => $params['categoria']));
          }

          if(isset($params['nome']) && !empty($params['nome'])){
            $select->where->like('tb_dashboard.nome', '%'.$params['nome'].'%');
          }

          $select->order('dc.ordem, tb_dashboard.ordem');
        }); 
    }

    public function getMenu($cliente){
      $dashboards = $this->getDashboardsByParams(array('cliente' => $cliente));
      $menu = array();
      $categoria = false;
      $cont = 0;
      foreach ($dashboards as $dashboard) {
        if(empty($dashboard['categoria'])){
          $menu[$cont] = array(
            'id'      =>  $dashboard['id'],
            'nome'    =>  $dashboard['nome'],
            'icone'   =>  $dashboard['icone'],
          );
          $menu[$cont]['dashboards'] = false;
          $cont++;
        }else{
          if($categoria != $dashboard['categoria']){
            $cont++;
            $categoria = $dashboard['categoria'];
            $menu[$cont] = array(
              'nome_categoria'  =>  $dashboard['nome_categoria'],
              'icone_categoria' =>  $dashboard['icone_categoria']
            );
            $menu[$cont]['dashboards'] = array();
          }
          $menu[$cont]['dashboards'][] = array(
            'id'      =>  $dashboard['id'],
            'nome'    =>  $dashboard['nome'],
            'icone'   =>  $dashboard['icone'],
          );

        }
      }
      
      return $menu;
    }

}
