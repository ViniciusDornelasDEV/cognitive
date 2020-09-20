<?php

namespace Invoice\Controller;

use Application\Controller\BaseController;
use Zend\View\Model\ViewModel;

use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Session\Container;
use Zend\File\Transfer\Adapter\Http as fileTransfer;

use Invoice\Form\Invoice as formInvoice;
use Invoice\Form\Pesquisa as formPesquisa;

class InvoiceController extends BaseController
{
    public function indexAction(){
      //verificar se é cliente
      $usuario = $this->getServiceLocator()->get('session')->read();
      if($usuario['id_usuario_tipo'] == 3){
        $this->layout('layout/cliente/admin');
      }

      if($usuario['id_usuario_tipo'] == 2){
        $this->layout('layout/edicao');
      }

      //instancia e pega parametross de form de pesquisa
    	$formPesquisa = new formPesquisa('frmPesquisa');
      $params = array();
      if($this->getRequest()->isPost()){
        $dados = $this->getRequest()->getPost();
        if(isset($dados['limpar'])){
          return $this->redirect()->toRoute('indexInvoice', array('page' => $this->params()->fromRoute('page')));
        }

        $formPesquisa->setData($dados);
        if($formPesquisa->isValid()){
          $params = $formPesquisa->getData();
        }
      }

      //seta cliente e realiza pesqusa
      $container = new Container();
      $params['cliente'] = $container->cliente['id'];
      $invoices = $this->getServiceLocator()->get('Invoice')->getInvoicesByParams($params)->toArray();
      
      $paginator = new Paginator(new ArrayAdapter($invoices));
      $paginator->setCurrentPageNumber($this->params()->fromRoute('page'));
      $paginator->setItemCountPerPage(10);
      $paginator->setPageRange(5);
      
      return new ViewModel(array(
          'invoices'      => $paginator,
          'formPesquisa'  => $formPesquisa,
          'cliente'       => $container->cliente,
          'usuario'       => $usuario   
      ));
    }

    public function novoAction(){
      $usuario = $this->getServiceLocator()->get('session')->read();
      if($usuario['id_usuario_tipo'] == 2){
        $this->layout('layout/edicao');
      }
    	$formInvoice = new formInvoice('frmInvoice');
      $container = new Container();
      
      if($this->getRequest()->isPost()){
        $formInvoice->setData($this->getRequest()->getPost());
        if($formInvoice->isValid()){
          $dados = $formInvoice->getData();

          //fazer upload de arquivo
          $file = $this->getRequest()->getfiles()->toArray();
          if(!empty($file['arquivo']['name'])){
            //fazer upload do arquivo
            $id = $this->getServiceLocator()->get('Invoice')->getNextInsertId('tb_invoice');
            $dados['arquivo'] = $this->uploadImagem($file, $container->cliente['id'], $id->Auto_increment);
          }

          //salvar invoice 
          $dados['cliente'] = $container->cliente['id'];
          $this->getServiceLocator()->get('Invoice')->insert($dados);
          $this->flashMessenger()->addSuccessMessage('Invoice inserido com sucesso!');
          return $this->redirect()->toRoute('indexInvoice');
        }
      }

    	return new ViewModel(array(
        'formInvoice' => $formInvoice,
        'cliente'     => $container->cliente
      ));
    }

    public function alterarAction(){
      $usuario = $this->getServiceLocator()->get('session')->read();
      if($usuario['id_usuario_tipo'] == 2){
        $this->layout('layout/edicao');
      }
      //pesquisar e validar invoice
      $container = new Container();
      $idInvoice = $this->params()->fromRoute('id');
      $invoice = $this->getServiceLocator()->get('Invoice')->getRecord($idInvoice);
      if(!$invoice){
        $this->flashMessenger()->addWarningMessage('Invoice não encontrado!');
        return $this->redirect()->toRoute('indexInvoice');
      }
      $formInvoice = new formInvoice('frmInvoice');
      
      //se veio post, alterar invoice
      if($this->getRequest()->isPost()){
        $formInvoice->setData($this->getRequest()->getPost());
        if($formInvoice->isValid()){
          $dados = $formInvoice->getData();
          unset($dados['arquivo']);
          $file = $this->getRequest()->getfiles()->toArray();
          if(!empty($file['arquivo']['name'])){
            //fazer upload do arquivo
            $dados['arquivo'] = $this->uploadImagem($file, $container->cliente['id'], $idInvoice);
          }

          $this->getServiceLocator()->get('Invoice')->update($dados, array('id' => $idInvoice));
          $this->flashMessenger()->addSuccessMessage('Invoice alterado com sucesso!');
          return $this->redirect()->toRoute('alterarInvoice', array('id' => $idInvoice));
        }
      }

      $formInvoice->setData($invoice);

      return new ViewModel(array(
        'formInvoice' =>  $formInvoice,
        'cliente'     =>  $container->cliente,
        'invoice'     =>  $invoice
      ));
    }

    public function enviaremailAction(){
      $container = new Container();
      if(empty($container->cliente['usuario_azure'])){
        $this->flashMessenger()->addWarningMessage('Favor inserir um email para o cliente!');
        return $this->redirect()->toRoute('alterarInvoice', array('id' => $this->params()->fromRoute('id')));
      }
      //enviar invoice por email
      $mailer = $this->getServiceLocator()->get('mailer');
      $link = $this->getRequest()->getUri()->getScheme() . '://' . $this->getRequest()->getUri()->getHost().'/invoice/download/'.$this->params()->fromRoute('id');
      $html = $mailer->emailInvoice($link);
      
      $mailer->mailUser($container->cliente['usuario_azure'], 'Cognitive, invoice', $html);
      $this->flashMessenger()->addSuccessMessage('Email enviado com sucesso!');
      return $this->redirect()->toRoute('alterarInvoice', array('id' => $this->params()->fromRoute('id')));

    }

    public function pagarinvoiceAction(){
      $invoice = $this->getServiceLocator()->get('Invoice')->getRecord($this->params()->fromRoute('id'));
      $container = new Container();
      if($invoice['cliente'] != $container->cliente['id']){
        $this->flashMessenger()->addWarningMessage('Invoice não encontrado');
        return $this->redirect()->toRoute('indexInvoice');
      }

      //pagar invoice
      $this->getServiceLocator()->get('Invoice')->update(array('pago' => 'S'), array('id' => $invoice['id']));
      $this->flashMessenger()->addSuccessMessage('Invoice pago com sucesso!');
      return $this->redirect()->toRoute('indexInvoice');
    }

    public function downloadinvoiceAction(){
      $invoice = $this->getServiceLocator()->get('Invoice')->getRecord($this->params()->fromRoute('id'));
      $container = new Container();

      if(!$invoice || $invoice['cliente'] != $container->cliente['id']){
        $this->flashMessenger()->addWarningMessage('Invoice não encontrado!');
        return $this->redirect()->toRoute('indexInvoice');
      }

      if(empty($invoice['arquivo'])){
        $this->flashMessenger()->addWarningMessage('Invoice sem arquivo!');
        return $this->redirect()->toRoute('indexInvoice');
      }

      $fileName = $invoice->arquivo;

      if(!is_file($fileName)) {
          //Não foi possivel encontrar o arquivo
      }
      $fileContents = file_get_contents($fileName);

      $response = $this->getResponse();
      $response->setContent($fileContents);

      $headers = $response->getHeaders();
      $headers->clearHeaders()
          ->addHeaderLine('Content-Type', 'whatever your content type is')
          ->addHeaderLine('Content-Disposition', 'attachment; filename="' . $fileName . '"')
          ->addHeaderLine('Content-Length', strlen($fileContents));
      return $this->response;
    }

    public function deletarinvoiceAction(){
      $invoice = $this->getServiceLocator()->get('Invoice')->getRecord($this->params()->fromRoute('id'));
      $container = new Container();

      if(!$invoice || $invoice['cliente'] != $container->cliente['id']){
        $this->flashMessenger()->addWarningMessage('Invoice não encontrado!');
        return $this->redirect()->toRoute('indexInvoice');
      }

      $this->getServiceLocator()->get('Invoice')->delete(array('id' => $invoice->id));
      $this->flashMessenger()->addSuccessMessage('Invoice excluído com sucesso!');
      return $this->redirect()->toRoute('indexInvoice');
    }

    private function uploadImagem($arquivo, $idCliente, $idInvoice){
        $upload_adapter = new fileTransfer();
        //Adicionando validators
        //$upload_adapter->addValidator('Size', false, '5242880');
        $extensao = $this->getExtensao($arquivo['arquivo']['name']);
        
        $caminho_arquivo = 'public/empresas/'.$idCliente;
        if(!file_exists($caminho_arquivo)){
            mkdir($caminho_arquivo);
        }

        $caminho_arquivo = 'public/empresas/'.$idCliente.'/invoices';
        if(!file_exists($caminho_arquivo)){
            mkdir($caminho_arquivo);
        }

        $caminho_arquivo = $caminho_arquivo.'/'.$idInvoice.'.'.$extensao;
        if(file_exists($caminho_arquivo)){
            unlink($caminho_arquivo);
        }

        $upload_adapter->addFilter('Rename', $caminho_arquivo);
        if(!$upload_adapter->receive()){
          $msg = '';
          foreach ($upload_adapter->getMessages() as $value) {
            $msg .= $value.'<br>';
          }
          $this->flashMessenger()->addWarningMessage($msg);
          return false; 
        }

        return 'public/empresas/'.$idCliente.'/invoices/'.$idInvoice.'.'.$extensao;
    }

}