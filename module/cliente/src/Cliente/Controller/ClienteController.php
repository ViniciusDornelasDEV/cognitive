<?php

namespace Cliente\Controller;

use Application\Controller\BaseController;
use Zend\View\Model\ViewModel;

use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Session\Container;
use Zend\File\Transfer\Adapter\Http as fileTransfer;
use Zend\Crypt\Password\Bcrypt;

use Cliente\Form\Cliente as formCliente;
use Cliente\Form\Usuario as formUsuario;
use Cliente\Form\Pesquisa as formPesquisa;
use Cliente\Form\AtivarUsuario as formAtivarUsuario;

class ClienteController extends BaseController
{

    public function indexAction(){
    	$formPesquisa = new formPesquisa('frmPesquisa');

      $params = array();
      if($this->getRequest()->isPost()){
        $dados = $this->getRequest()->getPost();
        
        if(isset($dados['limpar'])){
          return $this->redirect()->toRoute('indexCliente', array('page' => $this->params()->fromRoute('page')));
        }

        $formPesquisa->setData($dados);
        $params = $this->getRequest()->getPost();
      }
      $clientes = $this->getServiceLocator()->get('Cliente')->getClientesByParams($params)->toArray();
      
      $paginator = new Paginator(new ArrayAdapter($clientes));
      $paginator->setCurrentPageNumber($this->params()->fromRoute('page'));
      $paginator->setItemCountPerPage(10);
      $paginator->setPageRange(5);
      
      return new ViewModel(array(
          'clientes'          => $paginator,
          'clientesComplete'  => $clientes,
          'formPesquisa'      => $formPesquisa
      ));
    }

    public function novoAction(){
    	$formCliente = new formCliente('frmCliente');
      if($this->getRequest()->isPost()){
          $formCliente->setData($this->getRequest()->getPost());
          if($formCliente->isValid()){
            $dados = $formCliente->getData();

            //fazer upload de imagem
            $file = $this->getRequest()->getfiles()->toArray();
            if(empty($file['logo']['name'])){
                $dados['logo'] = '/img/semImagem.gif';
            }else{
                //fazer upload da imagem
                $id = $this->getServiceLocator()->get('Cliente')->getNextInsertId('tb_cliente');
                $dados['logo'] = $this->uploadImagem($file, $id->Auto_increment);
            } 

            //salvar cliente
         	  $result = $this->getServiceLocator()->get('Cliente')->insert($dados);
            if($result){
                $this->flashMessenger()->addSuccessMessage('Cliente inserido com sucesso!');                
                return $this->redirect()->toRoute('alterarCliente', array('id' => $result));
            }else{
                //falha, exibir mensagem
                $this->flashMessenger()->addErrorMessage('Falha ao inserir cliente!'); 
                return $this->redirect()->toRoute('novoCliente');
            }
          }

      }

    	return new ViewModel(array('formCliente' => $formCliente));
    }

    public function alterarAction(){
      //pegar parametros da url
      $idCliente = $this->params()->fromRoute('id');
      $idUsuario = $this->params()->fromRoute('usuario');

      //instanciar forms
      $formCliente = new formCliente('frmCliente');
      $formUsuario = new formUsuario('frmUsuario');

      //alimentar form de cliente
      $cliente = $this->getServiceLocator()->get('Cliente')->getRecord($idCliente);
      $formCliente->setData($cliente);

      //alimentar form de usuário e pesquisar usuários vinculados
      $usuario = false;
      if($idUsuario){
        $usuario = $this->getServiceLocator()->get('Usuario')->getRecordFromArray(array('id' => $idUsuario, 'cliente' => $idCliente));
        $formUsuario->setData($usuario);
      }

      //se veio post!
      if($this->getRequest()->isPost()){
        $dados = $this->getRequest()->getPost();
        if(isset($dados['cargo'])){
          $formUsuario->setData($dados);
          if($formUsuario->isValid()){
            if($usuario){
              //alterar usuário
              $this->getServiceLocator()->get('Usuario')->update($formUsuario->getData(), array('id' => $idUsuario));
              $this->flashMessenger()->addSuccessMessage('Usuário alterado com sucesso!');
              return $this->redirect()->toRoute('alterarCliente', array('id' => $idCliente, 'usuario' => $idUsuario));
            }else{
              //inserir usuário - Gerar token de ativação
              $dados = $formUsuario->getData();
              $idUsuario = $this->getServiceLocator()->get('Usuario')->getNextInsertId('tb_usuario');
              $dados['token_ativacao'] = strtolower(base64_encode(mt_rand().crypt(time().$idUsuario->Auto_increment.uniqid(mt_rand(), true))));
              $dados['ativo'] = 'A';
              $dados['id_usuario_tipo'] = 3;
              $dados['cliente'] = $idCliente;

              //salvar usuário na base de dados
              $idUsuario = $this->getServiceLocator()->get('Usuario')->insert($dados);

              //enviar link de ativação por email
              $mailer = $this->getServiceLocator()->get('mailer');
              $mailer->mailUser($dados['login'], 'Cognitive, ativação de conta', 'Seja bem vindo ao sistema cognitive, para ativar sua conta favor acessar o link abaixo.<br> '.
                $this->getRequest()->getUri()->getScheme() . '://' . $this->getRequest()->getUri()->getHost().
                '/cliente/ativar/'.$dados['token_ativacao']);

              //gerar mensagem de sucesso e redirecionar
              $this->flashMessenger()->addSuccessMessage('Usuário inserido com sucesso!');
              return $this->redirect()->toRoute('alterarCliente', array('id' => $idCliente, 'usuario' => $idUsuario));  
            }

          }
        }else{
          //alterar cliente
          $formCliente->setData($dados);
          if($formCliente->isValid()){
            $dados = $formCliente->getData();
            $file = $this->getRequest()->getfiles()->toArray();
            unset($dados['logo']);
            if(!empty($file['logo']['name'])){
              //fazer upload da imagem
              $dados['logo'] = $this->uploadImagem($file, $idCliente);
            }

            //alterar cliente
            $this->getServiceLocator()->get('Cliente')->update($dados, array('id' => $idCliente));
            $this->flashMessenger()->addSuccessMessage('Cliente alterado com sucesso!');
            return $this->redirect()->toRoute('alterarCliente', array('id' => $idCliente));
          }
        }
      }

      $usuarios = $this->getServiceLocator()->get('Usuario')->getRecordsFromArray(array('cliente' => $idCliente), 'nome, ativo DESC');

      return new ViewModel(array(
        'formCliente' => $formCliente,
        'formUsuario' => $formUsuario,
        'usuarios'    => $usuarios,
        'cliente'     =>  $cliente
      ));
    }

    public function ativarusuarioclienteAction(){
      $this->layout('layout/login');
      $token = $this->params()->fromRoute('token');
      $usuario = false;
      $formUsuario = false;
      //verificar se veio token  e se existe usuário com este token
      if(!empty($token)){
        $usuario = $this->getServiceLocator()->get('Usuario')->getRecord($token, 'token_ativacao');
        if($usuario){
          $formUsuario = new formAtivarUsuario('frmUsuario');
          //se não veio post, popular form
          $formUsuario->setData($usuario);

          //se vier post salvar dados
          if($this->getRequest()->isPost()){
            $dados = $this->getRequest()->getPost();
            $formUsuario->setData($dados);
            if($formUsuario->isValid()){
              $dados = $formUsuario->getData();
              //gerar senha e mudar parametros de config.
              $bcrypt = new bcrypt();
              $dados['senha'] = $bcrypt->create($dados['senha']);
              $dados['ativo'] = 'S';
              $dados['token_ativacao'] = '';
              
              //salvar alterações
              $this->getServiceLocator()->get('Usuario')->update($dados, array('token_ativacao' => $token));
              $this->flashMessenger()->addSuccessMessage('Usuário ativado com sucesso!');
              return $this->redirect()->toRoute('login');
            }
          }

        }
      }

      return new ViewModel(array(
        'usuario'     =>  $usuario,
        'formUsuario' =>  $formUsuario
      ));
    }

    public function deletarusuarioclienteAction(){
      //pegar parametros
      $idCliente = $this->params()->fromRoute('id');
      $idUsuario = $this->params()->fromRoute('usuario');

      //deletar usuário
      $this->getServiceLocator()->get('Usuario')->delete(array('id' => $idUsuario));
      $this->flashMessenger()->addSuccessMessage('Usuário excluído com sucesso!');
      return $this->redirect()->toRoute('alterarCliente', array('id' => $idCliente));
    }

    public function inativarclienteAction(){
      $idCliente = $this->params()->fromRoute('id');
      $this->getServiceLocator()->get('Cliente')->update(array('ativo' => 'N'), array('id' => $idCliente));
      $this->flashMessenger()->addSuccessMessage('Cliente inativado com sucesso!');
      return $this->redirect()->toRoute('indexCliente');
    }

    public function selecionarclienteAction(){
      $container = new Container();
      $cliente = $this->getServiceLocator()->get('Cliente')->getRecordFromArray(array(
        'id'    => $this->params()->fromRoute('id'),
        'ativo' => 'S'
      ));

      if($cliente){
        $container->cliente = $cliente;
      }else{
        $this->flashMessenger()->addWarningMessage('Cliente não encontrado ou inativo!');
      }

      return $this->redirect()->toRoute('indexCliente');
    }

    private function uploadImagem($arquivo, $idCliente){
        $upload_adapter = new fileTransfer();
        //Adicionando validators
        //$upload_adapter->addValidator('Size', false, '5242880');
        $container = new Container();
        //Decobrir extensao
        $extensao = $this->getExtensao($arquivo['logo']['name']);
        
        $caminho_arquivo = 'public/empresas/'.$idCliente;
        if(!file_exists($caminho_arquivo)){
            mkdir($caminho_arquivo);
        }

        $caminho_arquivo = $caminho_arquivo.'/logo.'.$extensao;
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

        return '/empresas/'.$idCliente.'/logo.'.$extensao;
    }

}