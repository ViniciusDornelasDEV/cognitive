<?php

namespace Invoice\Controller;

use Application\Controller\BaseController;
use Zend\View\Model\ViewModel;

use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Session\Container;

use Invoice\Form\Invoice as formInvoice;
use Invoice\Form\Pesquisa as formPesquisa;

class InvoiceController extends BaseController
{
    public function indexAction(){
    	$formPesquisa = new formPesquisa('frmPesquisa');
      $invoices = array();
      for ($i=0; $i < 10; $i++) { 
        $invoices[$i] = array('descricao' => 'Invoice de junho', 'valor' => 'R$ 500,00', 'data_referencia' => '15/06/2020');
      }
      $paginator = new Paginator(new ArrayAdapter($invoices));
      $paginator->setCurrentPageNumber($this->params()->fromRoute('page'));
      $paginator->setItemCountPerPage(10);
      $paginator->setPageRange(5);
      
      return new ViewModel(array(
          'invoices'      => $paginator,
          'formPesquisa'  => $formPesquisa
      ));
    }

    public function novoAction(){
    	$formInvoice = new formInvoice('frmInvoice');
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

    	return new ViewModel(array('formInvoice' => $formInvoice));
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
      $formInvoice = new formInvoice('frmInvoice');

      $formInvoice->setData(array(
        'descricao'        =>  'Invoice de junho',
        'valor'            =>  'R$ 500,00',
        'data_referencia'  =>  '15/06/2020'
      ));
      return new ViewModel(array(
        'formInvoice' => $formInvoice
      ));
    }

}