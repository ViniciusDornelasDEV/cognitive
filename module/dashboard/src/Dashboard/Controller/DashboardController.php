<?php

namespace Dashboard\Controller;

use Application\Controller\BaseController;
use Zend\View\Model\ViewModel;

use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Session\Container;
use Zend\File\Transfer\Adapter\Http as fileTransfer;

use Dashboard\Form\Dashboard as formDashboard;
use Dashboard\Form\Pesquisa as formPesquisa;
use Dashboard\Classes\PowerBI as powerBiApi; 
class DashboardController extends BaseController
{

    public function indexAction(){
    	//instancia e pega parametross de form de pesquisa
      $container = new Container();
      $formPesquisa = new formPesquisa('frmPesquisa', $this->getServiceLocator(), $container->cliente['id']);
      $params = array();
      if($this->getRequest()->isPost()){
        $dados = $this->getRequest()->getPost();
        if(isset($dados['limpar'])){
          return $this->redirect()->toRoute('indexDashboard', array('page' => $this->params()->fromRoute('page')));
        }

        $formPesquisa->setData($dados);
        if($formPesquisa->isValid()){
          $params = $formPesquisa->getData();
        }
      }

      //seta cliente e realiza pesqusa
      $params['cliente'] = $container->cliente['id'];
      $dashboards = $this->getServiceLocator()->get('Dashboard')->getDashboardsByParams($params)->toArray();
      
      $paginator = new Paginator(new ArrayAdapter($dashboards));
      $paginator->setCurrentPageNumber($this->params()->fromRoute('page'));
      $paginator->setItemCountPerPage(10);
      $paginator->setPageRange(5);
      return new ViewModel(array(
          'dashboards'      => $paginator,
          'formPesquisa'  => $formPesquisa,
          'cliente'       => $container->cliente
      ));
    }

    public function novoAction(){
      $container = new Container();
    	$formDashboard = new formDashboard('frmDashboard', $this->getServiceLocator(), $container->cliente['id']);
      
      if($this->getRequest()->isPost()){
        $formDashboard->setData($this->getRequest()->getPost());
        if($formDashboard->isValid()){
          $dados = $formDashboard->getData();

          //fazer upload de arquivo
          $file = $this->getRequest()->getfiles()->toArray();
          if(!empty($file['icone']['name'])){
            //fazer upload do arquivo
            $id = $this->getServiceLocator()->get('Dashboard')->getNextInsertId('tb_dashboard');
            $dados['icone'] = $this->uploadImagem($file, $container->cliente['id'], $id->Auto_increment);
          }

          //salvar cliente
          $dados['cliente'] = $container->cliente['id'];

          //colocar ultima na ordem
          $paramsOrdem = array('cliente' => $container->cliente['id']);
          if(empty($dados['categoria'])){
            $paramsOrdem['categoria'] = '';
          }else{
            $paramsOrdem['categoria'] = $dados['categoria'];
          }
          $dashboards = $this->getServiceLocator()->get('Dashboard')->getRecordsFromArray($paramsOrdem)->count();
          $dados['ordem'] = $dashboards+1;
          
          $this->getServiceLocator()->get('Dashboard')->insert($dados);
          $this->flashMessenger()->addSuccessMessage('Dashboard inserida com sucesso!');
          return $this->redirect()->toRoute('indexDashboard');
        }
      }

      return new ViewModel(array(
        'formDashboard' => $formDashboard,
        'cliente'       => $container->cliente
      ));
    }

    public function alterarAction(){
      //pesquisar e validar categoria
      $container = new Container();
      $idDashboard = $this->params()->fromRoute('id');
      $dashboard = $this->getServiceLocator()->get('Dashboard')->getRecord($idDashboard);
      if(!$dashboard){
        $this->flashMessenger()->addWarningMessage('Dashboard não encontrada!');
        return $this->redirect()->toRoute('indexDashboard');
      }
      $formDashboard = new formDashboard('frmDashboard', $this->getServiceLocator(), $container->cliente['id']);
      
      //se veio post, alterar categoria
      if($this->getRequest()->isPost()){
        $formDashboard->setData($this->getRequest()->getPost());
        if($formDashboard->isValid()){
          $dados = $formDashboard->getData();
          unset($dados['icone']);
          $file = $this->getRequest()->getfiles()->toArray();
          if(!empty($file['icone']['name'])){
            //fazer upload do arquivo
            $dados['icone'] = $this->uploadImagem($file, $container->cliente['id'], $idDashboard);
          }

          $this->getServiceLocator()->get('Dashboard')->update($dados, array('id' => $idDashboard));
          $this->flashMessenger()->addSuccessMessage('Dashboard alterada com sucesso!');
          return $this->redirect()->toRoute('indexDashboard');
        }
      }

      $formDashboard->setData($dashboard);
      return new ViewModel(array(
        'cliente'         =>  $container->cliente,
        'formDashboard'   =>  $formDashboard
      ));
    }

    public function deletarAction(){
      $this->getServiceLocator()->get('Dashboard')->delete(array('id' => $this->params()->fromRoute('id')));
      $this->flashMessenger()->addSuccessMessage('Dashboard excluído com sucesso!');
      return $this->redirect()->toRoute('indexDashboard');
    }

    public function visualizardashboardAction(){
      $dashboard = $this->getServiceLocator()->get('Dashboard')->getRecord($this->params()->fromRoute('id'));

      $powerBi = new powerBiApi();
      $embed = $powerBi->getUrl($dashboard['workspace_id'], $dashboard['report_id']);

      return new ViewModel(array(
        'dashboard' => $dashboard,
        'embed'     => $embed
      ));
    }

    private function uploadImagem($arquivo, $idCliente, $idDashboard){
        $upload_adapter = new fileTransfer();
        
        //Decobrir extensao
        $extensao = $this->getExtensao($arquivo['icone']['name']);
        
        $caminho_arquivo = 'public/empresas/'.$idCliente;
        if(!file_exists($caminho_arquivo)){
            mkdir($caminho_arquivo);
        }

        $caminho_arquivo = 'public/empresas/'.$idCliente.'/dashboards';
        if(!file_exists($caminho_arquivo)){
            mkdir($caminho_arquivo);
        }

        $caminho_arquivo = $caminho_arquivo.'/'.$idDashboard.'.'.$extensao;
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

        return 'public/empresas/'.$idCliente.'/dashboards/'.$idDashboard.'.'.$extensao;
    }

}