<?php

namespace Usuario\Controller;

use Application\Controller\BaseController;
use Usuario\Form\Login as loginForm;
use Zend\Authentication\Adapter\DbTable\CallbackCheckAdapter as AuthAdapter;
use Zend\Crypt\Password\Bcrypt;
use Zend\Authentication\Result;
use Zend\Session\SessionManager;

use Zend\View\Model\ViewModel;
use Zend\Authentication\AuthenticationService;

use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Role\GenericRole as Role;
use Zend\Permissions\Acl\Resource\GenericResource as Resource;
use Zend\Session\Container;
use Usuario\Form\Usuario as usuarioForm;
use Usuario\Form\PesquisaUsuario as pesquisaForm;
use Usuario\Form\AlterarSenha as alterarSenhaForm;
use Usuario\Form\RecuperarSenha as novaSenhaForm;
use Usuario\Form\AtivarUsuario as formAtivarUsuario;

use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Mail;

class UsuarioController extends BaseController
{
  public function loginAction()
  { 
    $this->layout('layout/login');
    $form = new loginForm();
      
      //Log in
      $request = $this->getRequest();
      $post = $request->getPost();
      
      if(!isset($post->login)) {
          if(isset($_POST['login'])){
              $post = $_POST;
          }else{
              //header("Location: http://www.rstconsultoria.com.br/");
              //die();
          }
      }
      
      if ($request->isPost()) {
          $form->setData($post);

          if ($form->isValid()) {

              $data = $form->getData();

              // Configure the instance with constructor parameters...

              $authAdapter = new AuthAdapter($this->getServiceLocator()
                                  ->get('db_adapter_main'), 'tb_usuario', 'login', 'senha', 
                                  function($dbCredential, $requestCredential) {
                                      $bcrypt = new Bcrypt();
                                      return $bcrypt->verify($requestCredential, $dbCredential);
              });
              //apenas ativo = S
              $select = $authAdapter->getDbSelect();
              $select->where('ativo = "S"');
              $authAdapter
                      ->setTableName('tb_usuario')
                      ->setIdentityColumn('login')
                      ->setCredentialColumn('senha');

              $authAdapter
                      ->setIdentity($data['login'])
                      ->setCredential($data['password']);    

              $result = $authAdapter->authenticate()->getCode();    
              
              
              $session = $this->getServiceLocator()->get('session'); 
             
              if ($result === Result::SUCCESS) {
                  //remember me?
                  if(isset($post->remember_me) && $post->remember_me == 1) {                     
                      $defaultNamespace = new SessionManager();
                      $defaultNamespace->rememberMe();
                  }            
                  
                  $user = (array)$authAdapter->getResultRowObject();    
                  $session->write($user);                                       
                  
                  //Create acl config
                  $sessao = new Container();
                  $sessao->acl = $this->criarAutorizacao();
                  if($user['id_usuario_tipo'] == 3){
                    //verificar se cliente está ativo
                    $cliente = $this->getServiceLocator()->get('Cliente')->getRecord($user['cliente']);
                
                    //se cliente ativo, redir para visualizar dashboards
                    if($cliente && $cliente['ativo'] == 'S'){
                      //selecionar cliente
                      $sessao->cliente = $cliente;
                      die('Cliente, redir para interface de visualizar!');
                    }else{
                      $this->flashMessenger()->addWarningMessage('Cliente não encontrado ou inativo!');
                      return $this->redirect()->toRoute('logout');
                    }
                  }else{
                    $sessao->cliente = $this->getServiceLocator()
                      ->get('Cliente')
                      ->getRecordsFromArray(array('ativo' => 'S'))
                      ->current();

                    if($user['id_usuario_tipo'] == 2){
                      die('Edição!');
                    }
                    return $this->redirect()->toRoute('indexCliente');
                  } 
              } else {
                //form invalido
                  $session->clear();
                  $this->flashMessenger()->addWarningMessage('Login ou senha inválidos!');
                  return $this->redirect()->toRoute('login');
              }
          }
      }        

      return new ViewModel(array('form' => $form));

  }

  public function logoutAction() {
      $sessao = new Container();
      $sessao->getManager()->getStorage()->clear();
      
      $session = $this->getServiceLocator()->get('session');  
      $defaultNamespace = new SessionManager();
      $defaultNamespace->destroy();
      $session->clear();

      return $this->redirect()->toRoute('login');
  }

  public function alterarsenhaAction() {
      $form = new alterarSenhaForm('frmUsuario');
      if($this->getRequest()->isPost()){
          $dados = $this->getRequest()->getPost();
          $form->setData($dados);
          if($form->isValid()){
              //Pegar usuário logado
              $serviceUsuario = $this->getServiceLocator()->get('Usuario');
              $usuario = $this->getServiceLocator()->get('session')->read();
              $bcrypt = new bcrypt();                

              if(!$bcrypt->verify($dados['senha_atual'], $usuario['senha'])){
                  $this->flashMessenger()->addWarningMessage('Senha atual não confere!');
                  return $this->redirect()->toRoute('alterarSenha');
              }
              //alterar senha
              $usuario['senha'] = $bcrypt->create($dados['senha']);
              unset($usuario['id']);
              if($serviceUsuario->alterarSenhaFuncionario($usuario)){
                  $this->flashMessenger()->addSuccessMessage('Senha alterada com sucesso!');  
                  return $this->redirect()->toRoute('logout');
              }else{
                  $this->flashMessenger()->addErrorMessage('Falha ao alterar senha!');
                  return $this->redirect()->toRoute('alterarSenha');
              }
              
          }
      }
      return new ViewModel(array('form' => $form));
  }

  public function recuperarsenhaAction(){
      $this->layout('layout/site');
      $form = new novaSenhaForm('frmRecuperaSenha');
      
      if($this->getRequest()->isPost()){
          $dados = $this->getRequest()->getPost();
          $form->setData($dados);
          if($form->isValid()){
              $bcrypt = new bcrypt();                
              //alterar senha
              $serviceUsuario = $this->getServiceLocator()->get('Usuario');
              $novaSenha = 'otp'.date('Y+m()ds').rand(0, 99999);
              $usuario = array('senha' => $bcrypt->create($novaSenha));

              if($serviceUsuario->updateSinc($usuario, array('email' => $dados->login),  $this->getServiceLocator()->get('session')->read())){
                  $this->flashMessenger()->addSuccessMessage('Verifique a nova senha em sua conta de e-mail!');  
                  $mailer = $this->getServiceLocator()->get('mailer');
                  $mailer->mailUser($dados->login, 'Recuperar senha', 'Sua nova senha de acesso ao sistema: '.$novaSenha);
                  return $this->redirect()->toRoute('login');
              }else{
                  $this->flashMessenger()->addErrorMessage('Falha ao recuperar senha!');
                  return $this->redirect()->toRoute('recuperarSenha');
              }

              
          }
          
      }
      
      

      return new ViewModel(array('form' => $form));
  }

  private function criarAutorizacao() {
      //pesquisar perfil de usuário
      $serviceUsuario = $this->getServiceLocator()->get('UsuarioTipo');
      $perfilUsuario = $serviceUsuario->getRecord($serviceUsuario->getIdentity('id_usuario_tipo'));
      //criando papel do usuário
      $acl = new Acl();
      $papel = new Role($perfilUsuario['perfil']);
      $acl->addRole($papel);

      //definindo recursos existentes no sistema
      $serviceRecurso = $this->getServiceLocator()->get('Recurso');
      $recursos = $serviceRecurso->fetchAll();
      
      foreach ($recursos as $resource) {
          $acl->addResource(new Resource($resource->nome));
      }

      //Adicionar permissões
      $recursosUsuario = $serviceRecurso->getRecursosByTipoUsuario(array('usuario_tipo' => $perfilUsuario['id']));
      
      
      foreach ($recursosUsuario as $resource) {
          $acl->allow($perfilUsuario['perfil'], $resource->nome);
      }
      return $acl;
  }

  public function indexAction(){
      $formPesquisa = new pesquisaForm('frmPesquisa', $this->getServiceLocator());
      $dados = false;
      if($this->getRequest()->isPost()){
          $dados = $this->getRequest()->getPost();
          $formPesquisa->setData($dados);
      }

      $serviceUsuario = $this->getServiceLocator()->get('Usuario');
      $usuarios = $serviceUsuario->getUsuariosByParams($dados);

      $Paginator = new Paginator(new ArrayAdapter($usuarios->toArray()));
      $Paginator->setCurrentPageNumber($this->params()->fromRoute('page'));
      $Paginator->setItemCountPerPage(10);
      $Paginator->setPageRange(5);
      
      return new ViewModel(array(
                              'usuarios'      => $Paginator, 
                              'formPesquisa'   => $formPesquisa,
                          ));
  }

  public function novoAction(){
    $formUsuario = new usuarioForm('frmUsuario', $this->getServiceLocator());

    if($this->getRequest()->isPost()){
      $formUsuario->setData($this->getRequest()->getPost());
      if($formUsuario->isValid()){
        //aplicar configurações e salvar
        $dados = $formUsuario->getData();
        $idUsuario = $this->getServiceLocator()->get('Usuario')->getNextInsertId('tb_usuario');
              $dados['token_ativacao'] = strtolower(base64_encode(mt_rand().crypt(time().$idUsuario->Auto_increment.uniqid(mt_rand(), true))));
        $dados['ativo'] = 'A';

        //salvar
        $idUsuario = $this->getServiceLocator()->get('Usuario')->insert($dados);

        //enviar link de ativação por email
        $mailer = $this->getServiceLocator()->get('mailer');
        $mailer->mailUser($dados['login'], 'Cognitive, ativação de conta', 'Seja bem vindo ao sistema cognitive, para ativar sua conta favor acessar o link abaixo.<br> '.
          $this->getRequest()->getUri()->getScheme() . '://' . $this->getRequest()->getUri()->getHost().
          '/usuario/ativar/'.$dados['token_ativacao']);

        //gerar mensagem de sucesso e redirecionar
        $this->flashMessenger()->addSuccessMessage('Usuário inserido com sucesso!');
        return $this->redirect()->toRoute('usuario');  
      }
    }

    return new ViewModel(array('formUsuario' => $formUsuario));
  }

  public function ativarusuarioAction(){
    $this->layout('layout/login');
    $token = $this->params()->fromRoute('token');
    $usuario = false;
    $formAtivar = false;
    //verificar se veio token  e se existe usuário com este token
    if(!empty($token)){
      $usuario = $this->getServiceLocator()->get('Usuario')->getRecord($token, 'token_ativacao');
      if($usuario){
        $formAtivar = new formAtivarUsuario('frmUsuario');
        //se não veio post, popular form
        $formAtivar->setData($usuario);

        //se vier post salvar dados
        if($this->getRequest()->isPost()){
          $dados = $this->getRequest()->getPost();
          $formAtivar->setData($dados);
          if($formAtivar->isValid()){
            $dados = $formAtivar->getData();
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
      'formAtivar' =>  $formAtivar
    ));
  }


  public function alterarAction(){
    $formUsuario = new usuarioForm('frmUsuario', $this->getServiceLocator());
    $usuario = $this->getServiceLocator()->get('Usuario')->getRecord($this->params()->fromRoute('id'));
    if($this->getRequest()->isPost()){
      $formUsuario->setData($this->getRequest()->getPost());
      if($formUsuario->isValid()){
        //salvar
        $this->getServiceLocator()->get('Usuario')->update($formUsuario->getData(), array('id' => $usuario['id']));

        //gerar mensagem de sucesso e redirecionar
        $this->flashMessenger()->addSuccessMessage('Usuário alterado com sucesso!');
        return $this->redirect()->toRoute('usuario');  
      }
    }
    $formUsuario->setData($usuario);

    return new ViewModel(array('formUsuario' => $formUsuario));
  }

  public function deletarusuarioAction(){
    $this->getServiceLocator()->get('Usuario')->delete(array('id' => $this->params()->fromRoute('id')));
    $this->flashMessenger()->addSuccessMessage('Usuário excluído com sucesso!');
    return $this->redirect()->toRoute('usuario');
  }



}

