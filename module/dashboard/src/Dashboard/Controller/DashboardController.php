<?php

namespace Dashboard\Controller;

use Application\Controller\BaseController;
use Zend\View\Model\ViewModel;

use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Session\Container;

use Dashboard\Form\Dashboard as formDashboard;
use Dashboard\Form\Pesquisa as formPesquisa;

class DashboardController extends BaseController
{

    public function indexAction(){
    	$formPesquisa = new formPesquisa('frmPesquisa');
      $dashboards = array();
      for ($i=0; $i < 10; $i++) { 
        $dashboards[$i] = array('menu' => 'Dashboard acessos', 'nome' => 'Dashboard '.$i);
      }
      $paginator = new Paginator(new ArrayAdapter($dashboards));
      $paginator->setCurrentPageNumber($this->params()->fromRoute('page'));
      $paginator->setItemCountPerPage(10);
      $paginator->setPageRange(5);
      
      return new ViewModel(array(
          'dashboards'      => $paginator,
          'formPesquisa'  => $formPesquisa
      ));
    }

    public function novoAction(){
    	$formDashboard = new formDashboard('formDashboard', $this->getServiceLocator());
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

    	return new ViewModel(array('formDashboard' => $formDashboard));
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
      $formDashboard = new formDashboard('frmDashboard');

      $formDashboard->setData(array(
        'menu'            =>  1,
        'nome'            =>  'Dashboard 1',
        'descricao'       =>  'Dashboard de teste'
      ));
      return new ViewModel(array(
        'formDashboard' => $formDashboard
      ));
    }

}