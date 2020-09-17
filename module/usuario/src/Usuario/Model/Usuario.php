<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Usuario\Model;

use Zend\Crypt\Password\Bcrypt;
use Application\Model\BaseTable;
use Zend\Db\TableGateway\TableGateway;
use Zend\Session\Container;

class Usuario Extends BaseTable {

    public function exchangeArray($data) {
        $this->id = (isset($data['id'])) ? $data['id'] : null;
        $this->login = (isset($data['login'])) ? $data['login'] : null;
        $this->senha = (isset($data['senha'])) ? $data['senha'] : null;
    }

    protected function getEncryptedPassword() {

        $bcrypt = new Bcrypt();
        return $bcrypt->create($this->senha);
    }

    public function updateCurrent($data) {

        $this->exchangeArray($data);

        $user = array(
            'login' => $this->login,
        );

        if ($this->senha) {
            $user['senha'] = $this->getEncryptedPassword();
        }

        $id = $this->getIdentity('id');

        if ($id) {
            (int) $id;
            return $this->update($user, array('id' => $id));
        } else {
            throw new \Exception('Usuário a ser alterado não foi encontrado!');
        }
    }

    public function generatePasswordResetToken(\ArrayObject $user) {

        $token = strtolower(base64_encode(mt_rand() . crypt(time() . $user->login . uniqid(mt_rand(), true))));

        $this->update(array('reset_token' => $token), array('id' => $user->id));

        return $token;
    }

    public function resetPassword(\ArrayObject $user, $data) {
        
        $this->senha = $data['senha'];
        
        $res = $this->update(
                array(
                    'senha' => $this->getEncryptedPassword(), 
                    'reset_token' => null), 
                array(
                    'id' => $user->id, 
                    'reset_token' => $data['reset_token']
                ));
        
        return $res;
        
    }

    public function getUserData($params) {
        $rowset = $this->getTableGateway()->select(function($select) use ($params) {
                    $select->join(
                                array('t' => 'tb_usuario_tipo'), 
                                't.id = tb_usuario.id_usuario_tipo', 
                                array('perfil'));
                    
                    $select->where($params);
                    
                }); 
        if (!$row = $rowset->current()) {
            return false;
        }
        return $row;
    }

    public function getUsuariosByParams($params = false){
        return $this->getTableGateway()->select(function($select) use ($params) {
            $select->join(
                    array('ut' => 'tb_usuario_tipo'),
                    'ut.id = id_usuario_tipo',
                    array('perfil')
                );

            if($params){
                if(!empty($params['nome'])){
                    $select->where->like('nome', '%'.$params['nome'].'%');
                }    

                if(!empty($params['id_usuario_tipo'])){
                    $select->where(array('id_usuario_tipo' => $params['id_usuario_tipo'])); 
                }
            }
            
            /*$select->where
                ->nest
                    ->notEqualTo('id_usuario_tipo', 3)
                    ->and
                    ->notEqualTo('id_usuario_tipo', 4)
                ->unnest;*/

            $select->order('nome');
        }); 
    }

    public function verificaEmail($idUsuario, $email){
        return $this->getTableGateway()->select(function($select) use ($idUsuario, $email) {

            $select->where(array('email' => $email));
            
            $select->where->notEqualTo('id', $idUsuario);
        })->current(); 
    }

    public function getUsuariosByCliente($params){
      return $this->getTableGateway()->select(function($select) use ($params) {
        $select->join(
                array('uc' => 'tb_usuario_cliente'),
                'uc.usuario = tb_usuario.id',
                array()
            );

        if($params){
            if(!empty($params['cliente'])){
                $select->where(array('cliente' => $params['cliente'])); 
            }

            if(isset($params['usuario']) && !empty($params['usuario'])){
                $select->where(array('uc.usuario' => $params['usuario'])); 
            }
        }

        $select->order('nome, ativo DESC');
        }); 
    }


    public function getClientesByUsuario($idUsuario, $idCliente = false){
      return $this->getTableGateway()->select(function($select) use ($idUsuario, $idCliente) {
        $select->join(
                array('uc' => 'tb_usuario_cliente'),
                'uc.usuario = tb_usuario.id',
                array()
            );

        $select->join(
                array('c' => 'tb_cliente'),
                'c.id = uc.cliente',
                array('nome_cliente' => 'nome', 'id_cliente' => 'id', 'logo', 'id_azure', 'usuario_azure', 'senha_azure', 'cliente_ativo' => 'ativo')
            );

        if($idCliente){
          $select->where(array('uc.cliente' => $idCliente));  
        }
        $select->where(array('uc.usuario' => $idUsuario, 'c.ativo' => 'S')); 

        $select->order('nome, ativo DESC');
        }); 
    }

    public function deletar($idUsuario){
      $adapter = $this->getTableGateway()->getAdapter();
      $connection = $adapter->getDriver()->getConnection();
      $connection->beginTransaction();

      try {
        $tbClientes = new TableGateway('tb_usuario_cliente', $adapter);
        $tbClientes->delete(array('usuario' => $idUsuario));
        parent::delete(array('id' => $idUsuario));
        $connection->commit();
        return true;
      } catch (Exception $e) {
        $connection->rollback();
        return false;
      }
      return false;
    }

}
