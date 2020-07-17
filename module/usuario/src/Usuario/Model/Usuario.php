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
            
            $select->where->notEqualTo('id_usuario_tipo', 2);

            $select->order('nome');
        }); 
    }

    public function verificaEmail($idUsuario, $email){
        return $this->getTableGateway()->select(function($select) use ($idUsuario, $email) {

            $select->where(array('email' => $email));
            
            $select->where->notEqualTo('id', $idUsuario);
        })->current(); 
    }

    public function alterarSenhaFuncionario($usuario){
        $adapter = $this->getTableGateway()->getAdapter();
        $connection = $adapter->getDriver()->getConnection();
        $connection->beginTransaction();
        try {
            //update na base
            parent::update($usuario, array('id' => $usuario['id_usuario_base']));

            //update na base default
            $adapter = $this->getTableGateway()->getAdapter();
            $adapterBase = parent::adapterBase();

            $tbUsuarioDafault = new TableGateway('tb_usuario', $adapterBase);
            $whereDefault = array('id_usuario_base' => $usuario['id_usuario_base'], 'database_name' => $usuario['database_name']);
            $tbUsuarioDafault->update($usuario, $whereDefault);

            $connection->commit();
            return true;
        } catch (Exception $e) {
            $connection->rollback();
            return false;
        }
    }

    public function updateSinc($dados, $usuario){
        $adapter = $this->getTableGateway()->getAdapter();
        $connection = $adapter->getDriver()->getConnection();
        $connection->beginTransaction();
        try {
            //update na base
            parent::update($dados, array('id' => $usuario['id']));

            if($usuario['id_usuario_tipo'] != 2){
                $container = new Container();
                //update na base default
                $adapter = $this->getTableGateway()->getAdapter();
                $adapterBase = parent::adapterBase();

                $tbUsuarioDafault = new TableGateway('tb_usuario', $adapterBase);
                $whereDefault = array('id_usuario_base' => $usuario['id'], 'database_name' => $container->empresa['database_name']);
                $tbUsuarioDafault->update($dados, $whereDefault);
            }

            $connection->commit();
            return true;
        } catch (Exception $e) {
            $connection->rollback();
            return false;
        }
    }

    public function insertSinc($dados, $usuario){
        $adapter = $this->getTableGateway()->getAdapter();
        $connection = $adapter->getDriver()->getConnection();
        $connection->beginTransaction();

        try {
            //insert na base
            $idUsuario = parent::insert($dados);

            //insert na base default
            $adapter = $this->getTableGateway()->getAdapter();
            $adapterBase = parent::adapterBase();

            $tbUsuarioDafault = new TableGateway('tb_usuario', $adapterBase);
            //$tbUsuarioDafault->insert($dados);

            $sql = 'INSERT INTO tb_usuario (nome, email, id_usuario_tipo, senha, id_usuario_base, database_name, codigo_confirmacao, empresa) VALUES ("'.$dados['nome'].'", "'.$dados['email'].'", 
                '.$dados['id_usuario_tipo'].', "'.$dados['senha'].'", "'.$idUsuario.'", "'.$usuario['database_name'].'", "S", '.$usuario['empresa'].');';

            $statement = $adapterBase->createStatement($sql);
            $result = $statement->execute();
            

            $connection->commit();
            return $idUsuario;
        } catch (Exception $e) {
            $connection->rollback();
            return false;
        }
    }

    public function registroRest($cliente, $dados){
        $adapter = $this->getTableGateway()->getAdapter();
        $connection = $adapter->getDriver()->getConnection();
        $connection->beginTransaction();
        try {
            //se existe cliente fazer update senão insert
            $tbCliente = new TableGateway('tb_cliente', $adapter);
        
            $arrayCliente = array(
                'nome'      =>  $dados['nome'],
                'email'     =>  $dados['email'],
                'telefone'  =>  $dados['telefone']
            );
        
            if($cliente){
                $idCliente = $cliente['id'];
                $tbCliente->update($arrayCliente, array('id' => $cliente['id']));
            }else{
                $tbCliente->insert($arrayCliente);
                $idCliente = $tbCliente->getLastInsertValue();
            }

            //inserir usuário
            $bcrypt = new Bcrypt();
            parent::insert(array(
                'nome'              =>  $dados['nome'],
                'telefone'          =>  $dados['telefone'],
                'email'             =>  $dados['email'],
                'senha'             =>  $bcrypt->create($dados['senha']),
                'id_usuario_tipo'   =>  2,
                'cliente'           =>  $idCliente
            ));
            
            $connection->commit();
            return $idCliente;
        } catch (Exception $e) {
            $connection->rollback();
            return false;
        }

    }

    public function meusDadosRest($cliente, $dados){
        $adapter = $this->getTableGateway()->getAdapter();
        $connection = $adapter->getDriver()->getConnection();
        $connection->beginTransaction();
        try {
            $tbCliente = new TableGateway('tb_cliente', $adapter);
            $tbCliente->update(array(
                'nome'      =>  $dados['nome'],
                'email'     =>  $dados['email'],
                'telefone'  =>  $dados['telefone']
            ), array('id' => $cliente['id']));

            //inserir usuário
            parent::update(array(
                'nome'              =>  $dados['nome'],
                'telefone'          =>  $dados['telefone'],
                'email'             =>  $dados['email']
            ), array('cliente' => $cliente['id']));
            
            $connection->commit();
            return array('status' => true, 'mensagem' => 'Dados cadastrais alterados com sucesso!');  
        } catch (Exception $e) {
            $connection->rollback();
            return array('status' => false, 'mensagem' => 'Ocorreu algum erro, tente novamente!');  
        }

    }

    public function verificarDuplicidade($telefone, $idCliente){
        return $this->getTableGateway()->select(function($select) use ($telefone, $idCliente) {
            if(strlen($telefone) == 15){
                //retirar o 9
                $telefone2 = str_replace(' 9', ' ', $telefone);
            }else{
                //inserir o 9
                $telefone2 = str_replace(' ', ' 9', $telefone);
            }
           
            $select->where
                ->like('telefone', '%'.$telefone.'%')
                ->or
                ->like('telefone', '%'.$telefone2.'%');
            
            $select->where->notEqualTo('cliente', $idCliente);

        })->current(); 
    }

    public function getUsuarioBaseByParams($params){
       $adapterBase = parent::adapterBase();

        $tbUsuario = new TableGateway('tb_usuario', $adapterBase);
        return $tbUsuario->select($params)->current();
    }

}
