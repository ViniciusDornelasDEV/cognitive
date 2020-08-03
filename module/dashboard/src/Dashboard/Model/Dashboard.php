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

    public function getDashboardsByParams($params, $cliente = false){
        return $this->getTableGateway()->select(function($select) use ($params, $cliente) {
          $select->join(
              array('dc' => 'tb_dashboard_categoria'),
              'dc.id = categoria',
              array('id_categoria' => 'id', 'nome_categoria' => 'nome', 'icone_categoria' => 'icone', 'ordem_categoria' => 'ordem'),
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

          if($cliente){
            $select->where(array('tb_dashboard.ativo' => 'S'));
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
            'ordem'   =>  $dashboard['ordem'],
            'ativo'   =>  $dashboard['ativo']
          );
          $menu[$cont]['dashboards'] = false;
          $cont++;
        }else{
          if($categoria != $dashboard['categoria']){
            $cont++;
            $categoria = $dashboard['categoria'];
            $menu[$cont] = array(
              'id_categoria'    =>  $dashboard['id_categoria'],
              'nome_categoria'  =>  $dashboard['nome_categoria'],
              'icone_categoria' =>  $dashboard['icone_categoria'],
              'ordem'           =>  $dashboard['ordem_categoria']
            );
            $menu[$cont]['dashboards'] = array();
          }
          $menu[$cont]['dashboards'][] = array(
            'id'      =>  $dashboard['id'],
            'nome'    =>  $dashboard['nome'],
            'icone'   =>  $dashboard['icone'],
            'ativo'   =>  $dashboard['ativo']
          );

        }
      }
      
      //ordenar o array de acordo com o campo 'ordem'
      $arrayOrdenado = array();
      foreach ($menu as $item) {
        $arrayOrdenado[$item['ordem']] = $item;
      }
      ksort($arrayOrdenado);

      return $arrayOrdenado;
    }

    public function salvarMenu($menu){
      $adapter = $this->getTableGateway()->getAdapter();
      $connection = $adapter->getDriver()->getConnection();
      $connection->beginTransaction();
      
      try {

          $tbCategoria = new TableGateway('tb_dashboard_categoria', $adapter);
          foreach ($menu as $key => $item) {
            if(strstr($item['id'], 'conf') != false){
              continue;
            }
            //é uma dashboard sem categoria?
            if(strstr($item['id'], 'cat') == false){
              //não pode ter children
              if(isset($item['children'])){
                return false;
              }

              //update da ordem da dashboard
              parent::update(array('ordem' => $key+1, 'categoria' => ''), array('id' => $item['id']));
            }else{
              //update da posição da categoria
              $idCategoria = explode('-', $item['id']);
              $tbCategoria->update(array('ordem' => $key+1), array('id' => $idCategoria[1]));
              if(isset($item['children'])){
                foreach ($item['children'] as $keyDash => $itemDash) {
                  parent::update(array('categoria' => $idCategoria[1], 'ordem' => $keyDash+1), array('id' => $itemDash['id']));
                }
              }
            }
          }
          $connection->commit();

          return true;
      } catch (Exception $e) {
          $connection->rollback();
          return false;
      } 
    }

}
