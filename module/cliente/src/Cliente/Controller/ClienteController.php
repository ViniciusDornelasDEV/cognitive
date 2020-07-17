<?php

namespace Cliente\Controller;

use Application\Controller\BaseController;
use Zend\View\Model\ViewModel;

use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Session\Container;

use Cliente\Form\Cliente as formCliente;
use Cliente\Form\Usuario as formUsuario;
use Cliente\Form\Pesquisa as formPesquisa;

class ClienteController extends BaseController
{

    public function indexAction(){
    	$formPesquisa = new formPesquisa('frmPesquisa');
      $clientes = array();
      for ($i=0; $i < 20; $i++) { 
        $clientes[$i] = array('id' => 1, 'nome' => 'Magazine Luiza');
      }
      $paginator = new Paginator(new ArrayAdapter($clientes));
      $paginator->setCurrentPageNumber($this->params()->fromRoute('page'));
      $paginator->setItemCountPerPage(10);
      $paginator->setPageRange(5);
      
      return new ViewModel(array(
          'clientes'      => $paginator,
          'formPesquisa'  => $formPesquisa
      ));
    }

    public function novoAction(){
    	$formCliente = new formCliente('frmCliente');
      /*if($this->getRequest()->isPost()){
          $formBairro->setData($this->getRequest()->getPost());
          if($formBairro->isValid()){
              $dados = $formBairro->getData();
          	$result = $this->getServiceLocator()->get('Bairro')->insert($dados);
              if($result){
                  $this->flashMessenger()->addSuccessMessage('Bairro inserido com sucesso!');                
                  return $this->redirect()->toRoute('indexBairro');
              }else{
                  //falha, exibir mensagem
                  $this->flashMessenger()->addErrorMessage('Falha ao inserir bairro!'); 
              }
          }

      }*/

    	return new ViewModel(array('formCliente' => $formCliente));
    }

    public function alterarAction(){
      /*$idBairro = $this->params()->fromRoute('id');
      $serviceBairro = $this->getServiceLocator()->get('Bairro');
      $formBairro = new formBairro('frmBairro', $this->getServiceLocator());

      $bairro = $serviceBairro->getRecord($idBairro);
      if(!$bairro){
      	$this->flashMessenger()->addWarningMessage('Bairro não encontrado!');
      	return $this->redirect()->toRoute('indexBairro');
      }

      $formBairro->setData($bairro);

      if($this->getRequest()->isPost()){
      	$formBairro->setData($this->getRequest()->getPost());
      	if($formBairro->isValid()){
              $dados = $formBairro->getData();

      		$serviceBairro->update($dados, array('id' => $idBairro));
              $this->flashMessenger()->addSuccessMessage('Bairro alterado com sucesso!');
      		return $this->redirect()->toRoute('indexBairro');
      	}
      }*/
      $formCliente = new formCliente('frmCliente');
      
      $formUsuario = new formUsuario('frmUsuario');
      $usuarios = array();
      for ($i=0; $i < 3; $i++) { 
        $usuarios[$i] = array(
          'id'    =>  $i,
          'nome'  =>  'Usuário '.$i,
          'email' =>  'usuario'.$i.'@gmail.com'
        );
      }

      $formCliente->setData(array(
        'nome'            =>  'Magazine Luiza',
        'cliente_azure'   =>  '12313sdf',
        'usuario_azure'   =>  'admin@magazineluiza.com',
        'senha_azure'     =>  'addas%4fvfjh'
      ));
      return new ViewModel(array(
        'formCliente' => $formCliente,
        'formUsuario' => $formUsuario,
        'usuarios'    => $usuarios
      ));
    }

}