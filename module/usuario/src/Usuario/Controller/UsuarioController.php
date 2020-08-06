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
                    $cliente = $this->getServiceLocator()->get('Cliente')->getRecordFromArray(array(
                      'id'    => $user['cliente'],
                      'ativo' => 'S'
                    ));
                
                    //se cliente ativo, redir para visualizar dashboards
                    if($cliente){
                      $sessao->cliente = $cliente;
                      //pesquisar uma dash do cliente
                      $dashboard = $this->getServiceLocator()->get('Dashboard')->getRecordFromArray(array(
                        'cliente' => $user['cliente'],
                        'ativo'   => 'S'
                      ));

                      if($dashboard){
                        return $this->redirect()->toRoute('visualizarDashboard', array('id' => $dashboard->id));
                      }else{
                        return $this->redirect()->toRoute('indexInvoice');
                      }
                    }else{
                      $this->flashMessenger()->addWarningMessage('Cliente não encontrado ou inativo!');
                      return $this->redirect()->toRoute('logout');
                    }
                  }else{
                    $sessao->cliente = $this->getServiceLocator()
                      ->get('Cliente')
                      ->getRecordsFromArray(array('ativo' => 'S'))
                      ->current();

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

  public function logingoogleAction(){
    $dados = $this->getRequest()->getPost();
    
    //pesquisar email na base de dados
    $usuario = $this->getServiceLocator()->get('Usuario')->getRecord($dados['userEmail'], 'login');
    $retorno = 'erro';
    if($usuario){
      $session = $this->getServiceLocator()->get('session'); 
      $usuario['google'] = true;
      $session->write($usuario);
      $sessao = new Container();
      $sessao->acl = $this->criarAutorizacao();
    
      if($usuario['id_usuario_tipo'] == 3 || $usuario['id_usuario_tipo'] == 4){
        //verificar se cliente está ativo
        $cliente = $this->getServiceLocator()->get('Cliente')->getRecordFromArray(array(
          'id'    => $usuario['cliente'],
          'ativo' => 'S'
        ));
    
        //se cliente ativo, redir para visualizar dashboards
        if($cliente){
          $sessao->cliente = $cliente;
          //pesquisar uma dash do cliente
          $dashboard = $this->getServiceLocator()->get('Dashboard')->getRecordFromArray(array(
            'cliente' => $usuario['cliente'],
            'ativo'   => 'S'
          ));

          if($dashboard){
            $retorno = $this->url()->fromRoute('visualizarDashboard', array('id' => $dashboard->id));
          }else{
            $retorno = $this->url()->fromRoute('indexInvoice');
          }
        }else{
          $this->flashMessenger()->addWarningMessage('Cliente não encontrado ou inativo!');
          $retorno = 'false';
        }
      }else{
        $sessao->cliente = $this->getServiceLocator()
          ->get('Cliente')
          ->getRecordsFromArray(array('ativo' => 'S'))
          ->current();
        $retorno = $this->url()->fromRoute('indexCliente');
      } 
    }
    
    $view = new ViewModel();
    $view->setTerminal(true);
    $view->setVariables(array('retorno' =>  $retorno));
    return $view;
  }

  public function loginmicrosoftAction(){
    $acao = $this->params()->fromRoute('acao');
    $client_id = "70ff2ef4-e265-4822-a326-b4d3570765f5";
    $redirect_uri = 'https://' . $this->getRequest()->getUri()->getHost().
                '/login/microsoft';
      $scopes = "wl.basic,wl.offline_access,wl.signin,wl.emails";
    if($acao && $acao == 'S'){
      header("Location: " . "https://login.live.com/oauth20_authorize.srf?client_id=" . $client_id . "&scope=bingads.manage&response_type=code&redirect_uri=" . $redirect_uri);
      die();
    }else{
      if($acao == 'N'){
        $dados = $this->getRequest()->getPost();
        print_r($dados['token']);
        $token = strstr($dados['token'], 'access_token=');
        $token = str_replace('access_token=', '', $token);
        $token = strstr($token, '&', true);
        if(isset($token)){
          //user granted permission
          //get access token using the authorization code
          $url = "https://login.live.com/oauth20_token.srf";
          $fields = array("client_id" => $client_id, "redirect_uri" => $redirect_uri, "client_secret" => 'wpF3MyPI470F_i0~Yxt--b_AZPA2amhIUf', "code" => $token, "grant_type" => "authorization_code");


          $fields_string = '';
          foreach($fields as $key=>$value) { $fields_string .= $key."=".$value."&"; }
          rtrim($fields_string, "&");
          $ch = curl_init();
         
          //retirar em produção
          curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
          curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
          
          curl_setopt($ch,CURLOPT_URL, $url);
          curl_setopt($ch,CURLOPT_HTTPHEADER, array("Content-Type: application/x-www-form-urlencoded"));
          curl_setopt($ch,CURLOPT_POST, count($fields));
          curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
          curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
          /*curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: '."Bearer ".$token,
          ));*/

          $result = curl_exec($ch);
          $result = json_decode($result);

          var_dump(curl_error($ch));
          curl_close($ch);
          var_dump($result);
          die();
          //this is the refresh token used to access Microsoft Live REST APIs
          $access_token = $result->access_token;
          $refresh_token = $result->refresh_token;
        
          echo file_get_contents("https://apis.live.net/v5.0/me?access_token=". $access_token);

        }else{
          echo "An error occured";
        }

      }else{
        $this->layout('layout/login');
        return new ViewModel();  
      }
      /*var_dump($this->serverUrl(true));
    die();
      $token = 'EwAoA61DBAAUzl/nWKUlBg14ZGcybuC4/OHFdfEAAYJrX5Ly5KtaLi20jEZP%2bUfqG28O1242UXqOM40HNXTysSPMwTqBZrStnlv/a75e28Y1FClc8kP19dLuTU9a/EBt/Ml/ngZ4isKImYxvbDwCgmvj/I/deKngzqZ4KSQIlIJK2Ai73q7ifkuYfzdDSAJoFqHGny2R6od9CtIrJG5tNE67tBLTgUqZcDMy/QcZOk13FtGUhQ2qFv2yV8o0vGZc0L8kOtq2ABWcVctsB720kpJrMv7OldFq4mnxITm0UvBcEOSa8b5t87zBkT79q0V%2bshygZJqeZOIRLNEofpkYu2dUz/7H4P/EQJyDosKJlJSszu2gvtXYKJjNAE57uS8DZgAACN%2bqCUqvLctk%2bAFebLSuk8eGgshlQGfkl72i87sThaHVYbFmGJ9OkPARb8GemGAApBHQdy4jBCHxijS9C2TQNL2PUwV4XXDRowXupL22tr8yn/NS4IbBN/l%2b9fOB5ZJVuCEmHpGIcmAsMN9lMSAO9So7uiCfktuB5Tn6UzEW0S%2bduVM66iNMQrcobPLwetFSUXBhGTXqOP8MQRCj1iMMpliwF1jKdzoNGg%2bhjmnlSsK/7FD4wI8FPxJxCc7aFmFgL2QTW414wy27ggyVBaWkusto/aZhymo5tUSChhQAlXs%2bT77%2b7OfqPnoIEu%2bUz%2bca7LW/%2bD6XqK/T0x5wZROi2hRAjjIZVL6KfugruHIFvsZNFL0zaGizkceFHpNMGRzDZRkMhSLqQ0kBePg/ijz2FFhn4eYwZt5yik1fKZoHcH0FO/dRwjGyrnOtAefvbrMIEmIjv3c4%2blPTdduFV9gLrKrBrE/r4P9zIVI6w8g2u02efYG1uaGoLQci3Bw/XRHJxffVoGUZtXiZGrM8qQm2AzYBw1IvfuYLmXMMqcCU2Xwp9ozrHrwXM/dyf%2bcg5wYt9R9Rsil4ZfDqrrNkuvctDoCj3Z4/kXbl9ddRWTWGExj4CN%2bIVqWekGifzzVY2s1KQQOdPuWDmdMvhLAXQVFxT4%2bGZ666suLgJw3bUPETEE1FLcU5Ag%3d%3d';
      if(isset($token)){
        //user granted permission
        //get access token using the authorization code
        $url = "https://login.live.com/oauth20_token.srf";
        $fields = array("client_id" => $client_id, "redirect_uri" => $redirect_uri, "client_secret" => 'painel@2020', "code" => $token, "grant_type" => "authorization_code");
        $fields_string = '';
        foreach($fields as $key=>$value) { $fields_string .= $key."=".$value."&"; }
        rtrim($fields_string, "&");
        $ch = curl_init();
        
        //retirar em produção
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_HTTPHEADER, array("Content-Type: application/x-www-form-urlencoded"));
        curl_setopt($ch,CURLOPT_POST, count($fields));
        curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);

        $result = curl_exec($ch);
        $result = json_decode($result);

        var_dump(curl_error($ch));
        curl_close($ch);
        var_dump($result);
        die();
        //this is the refresh token used to access Microsoft Live REST APIs
        $access_token = $result->access_token;
        $refresh_token = $result->refresh_token;
      
        echo file_get_contents("https://apis.live.net/v5.0/me?access_token=". $access_token);

      }else{
        echo "An error occured";
      }
      $view = new ViewModel();
      $view->setTerminal(true);
      $view->setVariables(array());
      return $view;*/
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
                    $mailer->mailUser($usuario->login, 'Cognitive, recuperar senha', 'Acesse o link paa recuperar a senha: <br>'.$base.'
                        <br>O link tem validade de uma hora!');
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

