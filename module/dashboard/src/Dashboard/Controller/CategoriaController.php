<?php

namespace Dashboard\Controller;

use Application\Controller\BaseController;
use Zend\View\Model\ViewModel;

use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Session\Container;
use Zend\File\Transfer\Adapter\Http as fileTransfer;

use Dashboard\Form\Categoria as formCategoria;

class CategoriaController extends BaseController
{

     public function indexAction(){
      $usuario = $this->getServiceLocator()->get('session')->read();
      if($usuario['id_usuario_tipo'] == 2){
        $this->layout('layout/edicao');
      }
      //seta cliente e realiza pesqusa
      $container = new Container();
      $categorias = $this->getServiceLocator()->get('CategoriaDashboard')->getRecordsFromArray(array('cliente' => $container->cliente['id']), 'ordem')->toArray();

      $paginator = new Paginator(new ArrayAdapter($categorias));
      $paginator->setCurrentPageNumber($this->params()->fromRoute('page'));
      $paginator->setItemCountPerPage(10);
      $paginator->setPageRange(5);
      
      return new ViewModel(array(
          'categorias'      => $paginator,
          'cliente'         => $container->cliente     
      ));
    }

    public function novoAction(){
      $usuario = $this->getServiceLocator()->get('session')->read();
      if($usuario['id_usuario_tipo'] == 2){
        $this->layout('layout/edicao');
      }
      $formCategoria = new formCategoria('frmCategoria');
      $container = new Container();
      
      if($this->getRequest()->isPost()){
        $formCategoria->setData($this->getRequest()->getPost());
        if($formCategoria->isValid()){
          $dados = $formCategoria->getData();

          //fazer upload de arquivo
          $file = $this->getRequest()->getfiles()->toArray();
          if(!empty($file['icone']['name'])){
            //fazer upload do arquivo
            $id = $this->getServiceLocator()->get('CategoriaDashboard')->getNextInsertId('tb_dashboard_categoria');
            $dados['icone'] = $this->uploadImagem($file, $container->cliente['id'], $id->Auto_increment);
          }else{
            $dados['icone'] = 'public/img/semIcone.png';
          }

          //salvar categoria
          $dados['cliente'] = $container->cliente['id'];

          //colocar ultima na ordem
          $categorias = $this->getServiceLocator()->get('CategoriaDashboard')->getRecordsFromArray(array('cliente' => $container->cliente['id']))->count();
          $dados['ordem'] = $categorias+1;
          
          $this->getServiceLocator()->get('CategoriaDashboard')->insert($dados);
          $this->flashMessenger()->addSuccessMessage('Categoria inserida com sucesso!');
          return $this->redirect()->toRoute('indexCategoria');
        }
      }

      return new ViewModel(array(
        'formCategoria' => $formCategoria,
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
      $idCategoria = $this->params()->fromRoute('id');
      $categoria = $this->getServiceLocator()->get('CategoriaDashboard')->getRecord($idCategoria);
      if(!$categoria){
        $this->flashMessenger()->addWarningMessage('Categoria não encontrado!');
        return $this->redirect()->toRoute('indexCategoria');
      }
      $formCategoria = new formCategoria('frmCategoria');
      
      //se veio post, alterar categoria
      if($this->getRequest()->isPost()){
        $formCategoria->setData($this->getRequest()->getPost());
        if($formCategoria->isValid()){
          $dados = $formCategoria->getData();
          unset($dados['icone']);
          $file = $this->getRequest()->getfiles()->toArray();
          if(!empty($file['icone']['name'])){
            //fazer upload do arquivo
            $dados['icone'] = $this->uploadImagem($file, $container->cliente['id'], $idCategoria);
          }

          $this->getServiceLocator()->get('CategoriaDashboard')->update($dados, array('id' => $idCategoria));
          $this->flashMessenger()->addSuccessMessage('Categoria alterada com sucesso!');
          return $this->redirect()->toRoute('indexCategoria');
        }
      }

      $formCategoria->setData($categoria);

      return new ViewModel(array(
        'formCategoria' =>  $formCategoria,
        'cliente'     =>  $container->cliente,
        'categoria'     =>  $categoria
      ));
    }

    public function deletarAction(){
      $idCategoria = $this->params()->fromRoute('id');
      $dashboards = $this->getServiceLocator()->get('Dashboard')->getRecordsFromArray(array('categoria' => $idCategoria));

      if($dashboards->count() > 0){
        $this->flashMessenger()->addWarningMessage('Existem dashboards vinculadas a esta categoria, não foi possível excluir!');
        return $this->redirect()->toRoute('indexCategoria');
      }

      $this->getServiceLocator()->get('CategoriaDashboard')->delete(array('id' => $idCategoria));
      $this->flashMessenger()->addSuccessMessage('Categoria de dashboard excluída com sucesso!');
      return $this->redirect()->toRoute('indexCategoria');
    }

    private function uploadImagem($arquivo, $idCliente, $idCategoria){
        $upload_adapter = new fileTransfer();
        
        //Decobrir extensao
        $extensao = $this->getExtensao($arquivo['icone']['name']);
        
        $caminho_arquivo = 'public/empresas/'.$idCliente;
        if(!file_exists($caminho_arquivo)){
            mkdir($caminho_arquivo);
        }

        $caminho_arquivo = 'public/empresas/'.$idCliente.'/categoriasDash';
        if(!file_exists($caminho_arquivo)){
            mkdir($caminho_arquivo);
        }

        $caminho_arquivo = $caminho_arquivo.'/'.$idCategoria.'.'.$extensao;
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

        return 'public/empresas/'.$idCliente.'/categoriasDash/'.$idCategoria.'.'.$extensao;
    }

}