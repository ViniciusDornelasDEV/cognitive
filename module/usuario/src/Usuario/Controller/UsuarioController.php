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
use Usuario\Form\AlterarToken as alterarToken;
use Cliente\Form\VincularCliente as formVincularCliente;
use Cliente\Form\Usuario as formUsuarioCliente;
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
                  
                  if($user['id_usuario_tipo'] == 3 || $user['id_usuario_tipo'] == 4){
                    //verificar se cliente está ativo
                    $cliente = $this->getServiceLocator()->get('Usuario')->getClientesByUsuario($user['id'])->current();
                    
                    if($cliente){
                      $sessao->cliente = $this->getServiceLocator()->get('Cliente')->getRecord($cliente['id_cliente']);
                    }else{
                      //não tem nenhum cliente vinculado!
                      return $this->redirect()->toRoute('logout');
                    }
                    
                    return $this->redirect()->toRoute('selecionarCliente');
                  }else{
                    $cliente = $this->getServiceLocator()->get('Cliente')->getRecords('S', 'ativo');
                    $sessao->cliente = $cliente->current();
                    return $this->redirect()->toRoute('selecionarCliente');
                  } 
              } else {
                //form invalido
                $session->clear();
                $this->flashMessenger()->addWarningMessage('Login ou senha inválidos!');
                return $this->redirect()->toRoute('login');
              }
          }
      }        
      $cliente = $this->getServiceLocator()->get('Usuario')->getClientesByUsuario(37)->current();
      
      return new ViewModel(array('form' => $form));

  }

  public function logingoogleAction(){
    $dados = $this->getRequest()->getPost();
    //pesquisar email na base de dados
    $usuario = $this->getServiceLocator()->get('Usuario')->getRecordFromArray(array('login' => $dados['userEmail'], 'ativo' => 'S'));
    $retorno = 'erro';
    if($usuario){
      $session = $this->getServiceLocator()->get('session'); 
      $usuario['google'] = true;
      $session->write($usuario);
      $sessao = new Container();
      $sessao->acl = $this->criarAutorizacao();
      if($usuario['id_usuario_tipo'] == 3 || $usuario['id_usuario_tipo'] == 4){
        //verificar se cliente está ativo
        $cliente = $this->getServiceLocator()->get('Usuario')->getClientesByUsuario($usuario['id'])->current();
                    
        if($cliente){
          $sessao->cliente = $this->getServiceLocator()->get('Cliente')->getRecord($cliente['id_cliente']);
          $retorno = $this->url()->fromRoute('selecionarCliente');
        }else{
          $this->flashMessenger()->addWarningMessage('Cliente não encontrado ou inativo!');
          $retorno = 'false';
        }
      }else{
        $sessao->cliente = $this->getServiceLocator()
          ->get('Cliente')
          ->getRecordsFromArray(array('ativo' => 'S'))
          ->current();
        $retorno = $this->url()->fromRoute('selecionarCliente');
      } 
    }
    
    $view = new ViewModel();
    $view->setTerminal(true);
    $view->setVariables(array('retorno' =>  $retorno));
    return $view;
  }

  public function loginmicrosoftAction(){
    $this->layout('layout/vazio');
    $acao = $this->params()->fromRoute('acao');
    $client_id = "70ff2ef4-e265-4822-a326-b4d3570765f5";
    $redirect_uri = 'https://' . $this->getRequest()->getUri()->getHost().
                '/login/microsoft';

      $scopes = "wl.basic,wl.offline_access,wl.signin,wl.emails";
    if($acao && $acao == 'S'){
      //header("Location: " . "https://login.live.com/oauth20_authorize.srf?client_id=" . $client_id . "&scope=" . $scopes . "&response_type=token&redirect_uri=" . $redirect_uri);

      $urls = 'https://login.live.com/oauth20_authorize.srf?client_id='.$client_id.'&scope=wl.signin%20wl.basic%20wl.emails%20wl.contacts_emails&response_type=code&redirect_uri='.$redirect_uri;
      header("Location: " .$urls);
      die();
    }

    //processar o login
    if(isset($_GET['code']) && $_GET['code'] != "") {
      $auth_code = $_GET["code"];
      $client_id = "70ff2ef4-e265-4822-a326-b4d3570765f5";
      $client_secret = "-Z9W.ASaEi4m2nK1_~F86.2.McK39Ye~8s";
      $redirect_uri = 'https://' . $this->getRequest()->getUri()->getHost().
                '/login/microsoft';

      $fields=array(
          'code'=>  urlencode($auth_code),
          'client_id'=>  urlencode($client_id),
          'client_secret'=>  urlencode($client_secret),
          'redirect_uri'=>  urlencode($redirect_uri),
          'grant_type'=>  urlencode('authorization_code')
      );

      $post = '';
      foreach($fields as $key=>$value) { $post .= $key.'='.$value.'&'; }
      $post = rtrim($post,'&');

      $curl = curl_init();
      curl_setopt($curl,CURLOPT_URL,'https://login.live.com/oauth20_token.srf');
      curl_setopt($curl,CURLOPT_POST,5);
      curl_setopt($curl,CURLOPT_POSTFIELDS,$post);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER,TRUE);
      curl_setopt($curl, CURLOPT_SSL_VERIFYPEER,0);
      $result = curl_exec($curl);
      curl_close($curl);
      $response =  json_decode($result);

      $accesstoken = $response->access_token;
      $get_profile_url='https://apis.live.net/v5.0/me?access_token='.$accesstoken;
      $xmlprofile_res = $this->curl_file_get_contents($get_profile_url);
      $profile_res = json_decode($xmlprofile_res, true);

      if($profile_res){
        $result = $this->processarLoginMicrosoft($profile_res);    
      }else{
        $this->flashMessenger()->addWarningMessage('Erro ao tentar logar-se com sua conta microsoft');
        return $this->redirect()->toRoute('login');
      }
    } 

    return new ViewModel(array('acao' =>  $acao));
  }

  public function curl_file_get_contents($url) {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_AUTOREFERER, TRUE);
    curl_setopt($curl, CURLOPT_HEADER, 0);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
    $data = curl_exec($curl);
    curl_close($curl);
    return $data;
    }

  private function processarLoginMicrosoft($user){
    //pesquisar email na base de dados
    $usuario = $this->getServiceLocator()->get('Usuario')->getRecordFromArray(array('login' => $user['emails']['account'], 'ativo' => 'S'));
    if($usuario){
      $session = $this->getServiceLocator()->get('session'); 
      $session->write($usuario);
      $sessao = new Container();
      $sessao->acl = $this->criarAutorizacao();
      if($usuario['id_usuario_tipo'] == 3 || $usuario['id_usuario_tipo'] == 4){
        //verificar se cliente está ativo
        $cliente = $this->getServiceLocator()->get('Usuario')->getClientesByUsuario($usuario['id'])->current();
                    
        if($cliente){
          $sessao->cliente = $this->getServiceLocator()->get('Cliente')->getRecord($cliente['id_cliente']);
          return $this->redirect()->toRoute('selecionarCliente');
        }else{
          $this->flashMessenger()->addWarningMessage('Cliente não encontrado ou inativo!');
          return $this->redirect()->toRoute('login');
        }
      }else{
        $sessao->cliente = $this->getServiceLocator()
          ->get('Cliente')
          ->getRecordsFromArray(array('ativo' => 'S'))
          ->current();
        return $this->redirect()->toRoute('selecionarCliente');
      } 
    }else{
      $this->flashMessenger()->addWarningMessage('Nenhum usuário com o email informado!');
          return $this->redirect()->toRoute('login');
    }
  }

  public function logoutAction() {
    $this->layout('layout/login');
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
              $bcrypt = new Bcrypt();                

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
      $this->layout('layout/login');
        $form = new novaSenhaForm('frmRecuperaSenha');
        
        if($this->getRequest()->isPost()){
            $dados = $this->getRequest()->getPost();
            $form->setData($dados);
            if($form->isValid()){
                $bcrypt = new bcrypt();                
                //alterar senha
                $serviceUsuario = $this->getServiceLocator()->get('Usuario');
                //pesquisar usuário por email
                $usuario = $serviceUsuario->getRecord($dados->login, 'login');
                if(!$usuario){
                  $this->flashMessenger()->addErrorMessage('Email não encontrado!');
                  return $this->redirect()->toRoute('recuperarSenha');
                }

                //gerar o token
                $token = date('is').sprintf('%07X', mt_rand(0, 0xFFFFFFF)).'+'.$usuario->id;
                
                //recuperar baseUrl
                $uri = $this->getRequest()->getUri();
                $base = sprintf('%s://%s', $uri->getScheme(), $uri->getHost());
                $base = $base.'/usuario/recuperarsenha/token/'.$token;
                if($serviceUsuario->update(array('token_recuperar' => $token, 'token_expira' => date('Y-m-d H:i',strtotime('+1 hour',strtotime(date('Y-m-d H:i'))))), array('id' => $usuario->id))){
                    $this->flashMessenger()->addSuccessMessage('Enviamos um link de recuperação para seu email!');  
                    $mailer = $this->getServiceLocator()->get('mailer');
                    $html = $mailer->emailRecuperarSenha($base);
                    $mailer->mailUser($usuario->login, 'Sigmaflow, recuperar senha', $html);
                    return $this->redirect()->toRoute('login');
                }else{
                    $this->flashMessenger()->addErrorMessage('Falha ao recuperar senha!');
                    return $this->redirect()->toRoute('recuperarSenha');
                }

                
            }   
        }
        return new ViewModel(array('form' => $form));
  }

  public function tokenrecuperarAction(){
        $this->layout('layout/login');

        //receber o token
        $token = $this->params()->fromRoute('token');

        //verificar  se existe esse token na base de dados, pesquisar usuário
        $usuario = $this->getServiceLocator()->get('Usuario')->getRecord($token, 'token_recuperar');
        
        //verificar se token é do usuário
        $idUsuario = explode('+', $token);
        $idUsuario = $idUsuario[1];
        if(!$usuario || $idUsuario != $usuario['id'] || empty($token)){
            $this->flashMessenger()->addWarningMessage('Token inválido!');
            return $this->redirect()->toRoute('recuperarSenha');
        }

        //verificar se não expirou
        if(strtotime(date('Y-m-d H:i')) < $usuario['token_expira']){
            $this->flashMessenger()->addWarningMessage('Token inválido!');
            return $this->redirect()->toRoute('recuperarSenha');
        }

        $form = new alterarToken('frmUsuario');
        if($this->getRequest()->isPost()){
            $dados = $this->getRequest()->getPost();
            $form->setData($dados);
            if($form->isValid()){
                //Pegar usuário logado
                $bcrypt = new bcrypt();                

                //alterar senha
                $dadosUsuario = array();
                $dadosUsuario['senha'] = $bcrypt->create($dados['senha']);
                $dadosUsuario['token_recuperar'] = '';
                $dadosUsuario['token_expira'] = '';
                if($this->getServiceLocator()->get('Usuario')->update($dadosUsuario, array('id' => $usuario['id']))){
                    $this->flashMessenger()->addSuccessMessage('Senha alterada com sucesso!');  
                    return $this->redirect()->toRoute('login');
                }else{
                    $this->flashMessenger()->addErrorMessage('Falha ao alterar senha!');
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
      $sessao = new Container();
      $tipoPesquisa = $this->params()->fromRoute('tipo');

      if($tipoPesquisa){
        $sessao->tipoPesquisa = $tipoPesquisa;
      }

      if(!isset($sessao->tipoPesquisa)){
        $sessao->tipoPesquisa = 'C';
      }

      $formPesquisa = new pesquisaForm('frmPesquisa', $this->getServiceLocator());
      $params = array('tipo' => $sessao->tipoPesquisa);
      if($this->getRequest()->isPost()){
        $dados = $this->getRequest()->getPost();
        if(isset($dados['limpar'])){
          return $this->redirect()->toRoute('usuario', array('page' => $this->params()->fromRoute('page')));
        }

        $formPesquisa->setData($dados);
        if($formPesquisa->isValid()){
          $params = $formPesquisa->getData();
          $params['tipo'] = $sessao->tipoPesquisa;
        }
      }

      $serviceUsuario = $this->getServiceLocator()->get('Usuario');
      $usuarios = $serviceUsuario->getUsuariosByParams($params);

      $Paginator = new Paginator(new ArrayAdapter($usuarios->toArray()));
      $Paginator->setCurrentPageNumber($this->params()->fromRoute('page'));
      $Paginator->setItemCountPerPage(10);
      $Paginator->setPageRange(5);
      
      return new ViewModel(array(
                              'usuarios'      => $Paginator, 
                              'formPesquisa'   => $formPesquisa,
                          ));
  }

  public function indexclienteAction(){
    $this->layout('layout/cliente/admin');
    $formPesquisa = new pesquisaForm('frmPesquisa', $this->getServiceLocator());
    $params = false;
    if($this->getRequest()->isPost()){
      $dados = $this->getRequest()->getPost();
      if(isset($dados['limpar'])){
        return $this->redirect()->toRoute('usuario', array('page' => $this->params()->fromRoute('page')));
      }

      $formPesquisa->setData($dados);
      if($formPesquisa->isValid()){
        $params = $formPesquisa->getData();
      }
    }

    $serviceUsuario = $this->getServiceLocator()->get('Usuario');
    $usuarioLogado = $this->getServiceLocator()->get('session')->read();
    $clientesAdmin = $this->getServiceLocator()->get('UsuarioCliente')->getRecords($usuarioLogado['id'], 'usuario', array('cliente'));
    $clientes = array();
    foreach ($clientesAdmin as $cliente) {
      $clientes[] = $cliente['cliente'];
    }

    $usuarios = $serviceUsuario->getUsuariosCliente($params, $clientes);

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
    $sessao = new Container();
    if(isset($sessao->tipoPesquisa) && $sessao->tipoPesquisa == 'C'){
      $formUsuario = new formUsuarioCliente('frmUsuario', $this->getServiceLocator());
    }else{
      $formUsuario = new usuarioForm('frmUsuario', $this->getServiceLocator());
    }

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
        if($dados['id_usuario_tipo'] == 3 || $dados['id_usuario_tipo'] == 4){
          $link = $this->getRequest()->getUri()->getScheme() . '://' . $this->getRequest()->getUri()->getHost().
          '/cliente/ativar/'.$dados['token_ativacao'];
        }else{
          $link = $this->getRequest()->getUri()->getScheme() . '://' . $this->getRequest()->getUri()->getHost().
          '/usuario/ativar/'.$dados['token_ativacao'];

        }
        $html = $mailer->emailAtivacao($link);
        $mailer->mailUser($dados['login'], 'Sigmaflow, ativação de conta', $html);

        //gerar mensagem de sucesso e redirecionar
        $this->flashMessenger()->addSuccessMessage('Usuário inserido com sucesso!');
        return $this->redirect()->toRoute('usuario');  
      }
    }

    return new ViewModel(array('formUsuario' => $formUsuario));
  }

  public function novoclienteAction(){
    $this->layout('layout/cliente/admin');
    $formUsuario = new formUsuarioCliente('frmUsuario', $this->getServiceLocator());

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
        $serviceUsuario = $this->getServiceLocator()->get('Usuario');
        $usuarioLogado = $this->getServiceLocator()->get('session')->read();
        $clientesAdmin = $this->getServiceLocator()->get('UsuarioCliente')->getRecords($usuarioLogado['id'], 'usuario', array('cliente'))->current();
        $this->getServiceLocator()->get('UsuarioCliente')->insert(array(
          'usuario'   =>  $idUsuario,
          'cliente'   =>  $clientesAdmin['cliente']
        ));

        //enviar link de ativação por email
        $mailer = $this->getServiceLocator()->get('mailer');
        $link = $this->getRequest()->getUri()->getScheme() . '://' . $this->getRequest()->getUri()->getHost().
          '/cliente/ativar/'.$dados['token_ativacao'];
        $html = $mailer->emailAtivacao($link);
        $mailer->mailUser($dados['login'], 'Sigmaflow, ativação de conta', $html);

        //gerar mensagem de sucesso e redirecionar
        $this->flashMessenger()->addSuccessMessage('Usuário inserido com sucesso!');
        return $this->redirect()->toRoute('usuarioAlterarCliente', array('id' => $idUsuario));  
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
            unset($dados['login']);
            
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
    $usuario = $this->getServiceLocator()->get('Usuario')->getRecord($this->params()->fromRoute('id'));
    
    //vincular cliente
    $formVincular = false;
    $clientesUsuario = false;
    if($usuario['id_usuario_tipo'] == 3 || $usuario['id_usuario_tipo'] == 4){
      $formVincular = new formVincularCliente('frmVincular', $this->getServiceLocator(), $usuario['id']);
      $clientesUsuario = $this->getServiceLocator()->get('Usuario')->getClientesByUsuario(array('usuario' => $usuario['id']));
      $formUsuario = new formUsuarioCliente('frmUsuario', $this->getServiceLocator());
    }else{
      $formUsuario = new usuarioForm('frmUsuario', $this->getServiceLocator());
    }

    if($this->getRequest()->isPost()){
      $dados = $this->getRequest()->getPost();
      if(isset($dados['cliente'])){
        $formVincular->setData($dados);
        if($formVincular->isValid()){
          $dados = $formVincular->getData();
          $this->getServiceLocator()->get('UsuarioCliente')->insert(array(
            'usuario'   =>  $usuario['id'],
            'cliente'   =>  $dados['cliente']
          ));
          $this->flashMessenger()->addSuccessMessage('Cliente vinculado ao usuário com sucesso!');
          return $this->redirect()->toRoute('usuarioAlterar', array('id' => $usuario['id']));
        }
      }else{
        $formUsuario->setData($this->getRequest()->getPost());
        if($formUsuario->isValid()){
          //salvar
          $this->getServiceLocator()->get('Usuario')->update($formUsuario->getData(), array('id' => $usuario['id']));

          //gerar mensagem de sucesso e redirecionar
          $this->flashMessenger()->addSuccessMessage('Usuário alterado com sucesso!');
          return $this->redirect()->toRoute('usuarioAlterar');  
        }
      }
    }
    $formUsuario->setData($usuario);

    return new ViewModel(array(
      'formUsuario'     => $formUsuario,
      'formVincular'    => $formVincular,
      'clientesUsuario' => $clientesUsuario,
      'usuario'         => $usuario
    ));
  }

  public function alterarclienteAction(){
    $this->layout('layout/cliente/admin');
    $formUsuario = new usuarioForm('frmUsuario', $this->getServiceLocator());
    $usuarioLogado = $this->getServiceLocator()->get('session')->read();
    
    //verificar se usuário é realmente do cliente logado
    $clientesAdmin = $this->getServiceLocator()->get('UsuarioCliente')->getRecords($usuarioLogado['id'], 'usuario', array('cliente'));
    $clientes = array();
    foreach ($clientesAdmin as $cliente) {
      $clientes[] = $cliente['cliente'];
    }

    $usuario = $this->getServiceLocator()->get('Usuario')->getUsuariosCliente(
      array(),
      $clientes,
      $this->params()->fromRoute('id')
    )->current();
    if(!$usuario){
      $this->flashMessenger()->addWarningMessage('Usuário não encontrado!');
      return $this->redirect()->toRoute('usuarioCliente');
    }

    //vincular cliente
    $formVincular = false;
    $clientesUsuario = false;
    if($usuario['id_usuario_tipo'] == 3 || $usuario['id_usuario_tipo'] == 4){
      $formVincular = new formVincularCliente('frmVincular', $this->getServiceLocator(), $usuario['id'], $usuarioLogado);
      $clientesUsuario = $this->getServiceLocator()->get('Usuario')->getClientesByUsuario(array('usuario' => $usuario['id']));
    }

    if($this->getRequest()->isPost()){
      $dados = $this->getRequest()->getPost();
      if(isset($dados['cliente'])){
        $formVincular->setData($dados);
        if($formVincular->isValid()){
          $dados = $formVincular->getData();

          if(!in_array($dados['cliente'], $clientes)){
            $this->flashMessenger()->addWarningMessage('Cliente não encontrado!');
            return $this->redirect()->toRoute('usuarioAlterarCliente', array('id' => $usuario['id']));  
          }
          $this->getServiceLocator()->get('UsuarioCliente')->insert(array(
            'usuario'   =>  $usuario['id'],
            'cliente'   =>  $dados['cliente']
          ));
          $this->flashMessenger()->addSuccessMessage('Cliente vinculado ao usuário com sucesso!');
          return $this->redirect()->toRoute('usuarioAlterarCliente', array('id' => $usuario['id']));
        }
      }else{
        $formUsuario->setData($this->getRequest()->getPost());
        if($formUsuario->isValid()){
          //salvar
          $this->getServiceLocator()->get('Usuario')->update($formUsuario->getData(), array('id' => $usuario['id']));

          //gerar mensagem de sucesso e redirecionar
          $this->flashMessenger()->addSuccessMessage('Usuário alterado com sucesso!');
          return $this->redirect()->toRoute('usuarioAlterarCliente', array('id' => $usuario['id']));  
        }
      }
    }
    $formUsuario->setData($usuario);

    return new ViewModel(array(
      'formUsuario'     => $formUsuario,
      'formVincular'    => $formVincular,
      'clientesUsuario' => $clientesUsuario,
      'usuario'         => $usuario
    ));
  }

  public function deletarusuarioAction(){
    $this->getServiceLocator()->get('Usuario')->delete(array('id' => $this->params()->fromRoute('id')));
    $this->flashMessenger()->addSuccessMessage('Usuário excluído com sucesso!');
    return $this->redirect()->toRoute('usuario');
  }

  public function salvartemplateAction(){
    $template = $this->getRequest()->getPost();
    $usuario = $this->getServiceLocator()->get('session')->read();
    $usuario['template'] = $template['nomeTemplate'];
    
    $this->getServiceLocator()->get('session')->write($usuario);
    $this->getServiceLocator()->get('Usuario')
      ->update(array('template' => $template['nomeTemplate']), array('id' => $usuario['id']));

    $view = new ViewModel();
    $view->setTerminal(true);
    $view->setVariables(array());
    return $view;
  }

  public function salvarmenuAction(){
    $menu = $this->getRequest()->getPost();

    $usuario = $this->getServiceLocator()->get('session')->read();
    $usuario['menu_hidden'] = $menu['hidden'];
    
    $this->getServiceLocator()->get('session')->write($usuario);
    $this->getServiceLocator()->get('Usuario')
      ->update(array('menu_hidden' => $menu['hidden']), array('id' => $usuario['id']));

    $view = new ViewModel();
    $view->setTerminal(true);
    $view->setVariables(array());
    return $view;
  }

}

