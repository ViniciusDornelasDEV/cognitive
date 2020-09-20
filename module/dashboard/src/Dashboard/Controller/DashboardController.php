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
      $usuario = $this->getServiceLocator()->get('session')->read();
      if($usuario['id_usuario_tipo'] == 2){
        $this->layout('layout/edicao');
      }
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

    private function linkPowerBi($dados){
      $url = explode('/', $dados['link_power_bi']);
      unset($dados['link_power_bi']);
      foreach ($url as $key => $param) {
        if($param == 'groups'){
          $dados['workspace_id'] = $url[$key+1];
        }

        if($param == 'reports'){
          $dados['report_id'] = $url[$key+1];
        }        
      }
      return $dados;
    }

    public function novoAction(){
      $usuario = $this->getServiceLocator()->get('session')->read();
      if($usuario['id_usuario_tipo'] == 2){
        $this->layout('layout/edicao');
      }
      $container = new Container();
    	$formDashboard = new formDashboard('frmDashboard', $this->getServiceLocator(), $container->cliente['id']);
      
      if($this->getRequest()->isPost()){
        $formDashboard->setData($this->getRequest()->getPost());
        if($formDashboard->isValid()){
          $dados = $this->linkPowerBi($formDashboard->getData());

          //fazer upload de arquivo
          $file = $this->getRequest()->getfiles()->toArray();
          if(!empty($file['icone']['name'])){
            //fazer upload do arquivo
            $id = $this->getServiceLocator()->get('Dashboard')->getNextInsertId('tb_dashboard');
            $dados['icone'] = $this->uploadImagem($file, $container->cliente['id'], $id->Auto_increment);
          }else{
            $dados['icone'] = 'public/img/semIcone.png';
          }

          //salvar cliente
          $dados['cliente'] = $container->cliente['id'];

          //colocar ultima na ordem
          $paramsOrdem = array('cliente' => $container->cliente['id']);
          $dashboards = $this->getServiceLocator()->get('Dashboard')->getRecordsFromArray($paramsOrdem)->count();
          $dados['ordem'] = $dashboards+1;
          
          $this->getServiceLocator()->get('Dashboard')->insert($dados);
          $this->flashMessenger()->addSuccessMessage('Dashboard inserida com sucesso!');
          return $this->redirect()->toRoute('indexDashboard');
        }
      }

      $formDashboard->setData(array('ativo' => 'N'));
      return new ViewModel(array(
        'formDashboard' => $formDashboard,
        'cliente'       => $container->cliente
      ));
    }

    public function alterarAction(){
      $usuario = $this->getServiceLocator()->get('session')->read();
      if($usuario['id_usuario_tipo'] == 2){
        $this->layout('layout/edicao');
      }
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
          
          if(!empty($dados['link_power_bi'])){
            $dados = $this->linkPowerBi($dados);
          }

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

      if(!empty($dashboard['workspace_id']) && !empty($dashboard['report_id'])){
        $dashboard['link_power_bi'] = 'https://app.powerbi.com/groups/'.$dashboard['workspace_id'].'/reports/'.$dashboard['report_id'];
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
      $usuario = $this->getServiceLocator()->get('session')->read();
      if($usuario['id_usuario_tipo'] == 2){
        $this->layout('layout/edicao');
      }
      $dashboard = $this->getServiceLocator()->get('Dashboard')->getRecord($this->params()->fromRoute('id'));
      $container = new Container();
      
      //verificar se dash é da empresa selecionada
      if(!$dashboard || $container->cliente['id'] != $dashboard['cliente']){
        $this->flashMessenger()->addWarningMessage('A dashboard não pertence a eempresa selecionada ou não existe!');  
        return $this->redirect()->toRoute('indexDashboard');
      }
      
      //verificar se é cliente
      $usuario = $this->getServiceLocator()->get('session')->read();
      if($usuario['id_usuario_tipo'] == 3){
        $this->layout('layout/cliente/admin');
      }

      if($usuario['id_usuario_tipo'] == 4){
        $this->layout('layout/cliente');
      }

      $embed = false;
      if(!empty($dashboard['workspace_id']) && !empty($dashboard['report_id'])){
        if(empty($container->cliente['id_azure']) || empty($container->cliente['usuario_azure']) || empty($container->cliente['senha_azure'])){
          $this->flashMessenger()->addWarningMessage('Credenciais do powerBI não cadastradas, favor inserir as credenciais e selecionar o cliente novamente!');
          return $this->redirect()->toRoute('indexCliente');
        }
        $powerBi = new powerBiApi($container->cliente);
        $embed = $powerBi->getUrl($dashboard['workspace_id'], $dashboard['report_id']);
      }

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