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
use Usuario\Form\AlterarUsuario as alterarUsuarioForm;
use Usuario\Form\PesquisaUsuario as pesquisaForm;
use Usuario\Form\AlterarSenha as alterarSenhaForm;
use Usuario\Form\RecuperarSenha as novaSenhaForm;
use Usuario\Form\Registrese as formRegistro;

use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Mail;

class UsuarioController extends BaseController
{
    public function loginAction()
    {   
        $sessao = new Container();
                    
        $usuario = $this->getServiceLocator()->get('session')->read();

        if($usuario){
            return $this->redirect()->toRoute('logout');
        }

        $this->layout('layout/login');
        $form = new loginForm();
        
        //Log in
        $request = $this->getRequest();
        $post = $request->getPost();
        
        if(!isset($post->login)) {
            if(isset($_POST['senha'])){
                $post = $_POST;
            }
        }
        
        $reCaptchaValid = true;
        if ($request->isPost()) {
            if(empty($post['email'])){
                $this->flashMessenger()->addWarningMessage('Informe um telefone ou um email para realizar login!');
                return $this->redirect()->toRoute('login');
            }

            if(!empty($post['email'])){
                $login = 'email';
            }
            $form->setData($post);
            if ($form->isValid()) {
                if($_POST['g-recaptcha-response']){
                    $resposta = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=6LeEXPMUAAAAAHM_HPlA8rHQheBGawjGedKYx7KX&response=".$_POST['g-recaptcha-response']."&remoteip=".$_SERVER['REMOTE_ADDR']);
                    $resposta = json_decode($resposta);
                    
                    $data = $form->getData();

                    // Configure the instance with constructor parameters...

                    $authAdapter = new AuthAdapter($this->getServiceLocator()
                                        ->get('db_adapter_main'), 'tb_usuario', $login, 'senha', 
                                        function($dbCredential, $requestCredential) {
                                            $bcrypt = new Bcrypt();
                                            return $bcrypt->verify($requestCredential, $dbCredential);
                    });
                    
                    //apenas ativo = S
                    $select = $authAdapter->getDbSelect();
                    $select->where('ativo = "S"');

                    $authAdapter
                            ->setTableName('tb_usuario')
                            ->setIdentityColumn($login)
                            ->setCredentialColumn('senha');

                    $authAdapter
                            ->setIdentity($data[$login])
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
                        
                        if($user['codigo_confirmacao'] != 'S'){
                            $this->flashMessenger()->addWarningMessage('Favor confirmar seu email antes de realizar login!');
                             return $this->redirect()->toRoute('login');
                        }

                        $session->write($user);                                       
                        //Create acl config
                        $container = new Container();
                        $container->database = $user['database_name'];

                        //pesquisar a empresa
                        $container->empresa = $this->getServiceLocator()->get('Empresa')->getEmpresaByParams(array('id' => $user['empresa']));

                        $serviceUsuario = $this->getServiceLocator()->get('UsuarioTipo');
                        $container->acl = $this->criarAutorizacao();                            

                        //pesquisar empresa da base do cliente
                        $empresa = $this->getServiceLocator()->get('Empresa')->getEmpresaCliente($sessao->empresa['database_name']);
                        $container = new Container();
                        $container->empresa['senha_cancelamento'] = $empresa['senha_cancelamento'];
                        $container->empresa['senha_alteracao'] = $empresa['senha_alteracao'];
                        $container->empresa['finalizar_pedido_um_clique'] = $empresa['finalizar_pedido_um_clique'];
                        if(!empty($empresa['ifood_user'])){
                            $container->empresa['ifood_user'] = $empresa['ifood_user'];
                            $container->empresa['ifood_senha'] = $empresa['ifood_senha'];
                            $container->empresa['ifood_merchant_id'] = $empresa['ifood_merchant_id'];
                        }
                        
                        //se superadmin, cadastro de novas empresas!
                        if($user['id_usuario_tipo'] == 7){
                            return $this->redirect()->toRoute('logout');

                            //return $this->redirect()->toRoute('exportarIfoodSuperAdmin');
                        }
                        
                        //SE FOR COZINHA
                        if($user['id_usuario_tipo'] == 3){
                            return $this->redirect()->toRoute('cozinha');
                        }

                        //SE FOR ENTREGA
                        if($user['id_usuario_tipo'] == 4){
                            return $this->redirect()->toRoute('saidasEntrega');
                        }

                        //SE FOR ATENDENTE
                        if($user['id_usuario_tipo'] == 5){
                            return $this->redirect()->toRoute('listaPedidosCaixa');
                        }

                        //se for garçom
                        if($user['id_usuario_tipo'] == 6){
                            return $this->redirect()->toRoute('pedidosLojaGarcom');
                        }                    

                        return $this->redirect()->toRoute('telaInicio');
                        
                    } else {
                    	//form invalido
                        $session->clear();
                        $this->flashMessenger()->addWarningMessage('Login ou senha inválidos!');
                        return $this->redirect()->toRoute('login');
                    }
                }
            }
        } else{
            $usuario = $this->getServiceLocator()->get('session')->read();
            if($usuario){
                if($usuario['id_usuario_tipo'] == 2){
                    return $this->redirect()->toRoute('sigla');
                }else{
                    return $this->redirect()->toRoute('logout');
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
        $empresa = $this->getServiceLocator()->get('Empresa')->getRecord(1);
        $formUsuario = new usuarioForm('frmUsuario', $this->getServiceLocator(), $empresa['dominio']);
        //caso venha um post salvar
        if($this->getRequest()->isPost()){
            //salvar e enviar para  edit
            $dados = $this->getRequest()->getPost();
            $serviceUsuario = $this->getServiceLocator()->get('Usuario');
            
            //validar form
            $formUsuario->setData($dados);
            if($formUsuario->isValid()){  
                $bcrypt = new Bcrypt();
                $dados = $formUsuario->getData();
                $dados['senha'] = $bcrypt->create($dados['senha']);

                if(!empty($dados['email'])){
                    $usuario = $serviceUsuario->getRecord($dados['email'], 'email');
                    if($usuario){
                        $this->flashMessenger()->addWarningMessage('Já existe usuário cadastrado para o email '.$dados['email'].'.');
                        return $this->redirect()->toRoute('usuarioNovo');
                    }else{
                        if($dados['id_usuario_tipo'] == 2){
                            $this->flashMessenger()->addErrorMessage('Insira cliente pelo caixa!');
                            return $this->redirect()->toRoute('usuario');
                        }
                        //se tentar criar um superadmin retorna erro
                        if($dados['id_usuario_tipo'] == 7){
                            $this->flashMessenger()->addErrorMessage('Ocorreu algum erro, por favor tente novamente!');
                            return $this->redirect()->toRoute('usuario');
                        }
                        $result = $serviceUsuario->insertSinc($dados, $this->getServiceLocator()->get('session')->read());
                        if($result){
                            
                            //sucesso criar mensagem e redir para edit
                            $this->flashMessenger()->addSuccessMessage('Usuário inserido com sucesso!');                
                            return $this->redirect()->toRoute('usuarioAlterar', array('id' => $result));
                        }else{
                            //falha, exibir mensagem
                            $this->flashMessenger()->addErrorMessage('Falha ao inserir usuário!');
                        }
                    }
                }
            }

        }

        return new ViewModel(array('formUsuario' => $formUsuario));
    }


    public function alterarAction(){
        //Pesquisar cliente
        $idUsuario = $this->params()->fromRoute('id');
        $serviceUsuario = $this->getServiceLocator()->get('Usuario');
        $usuario = $serviceUsuario->getRecordFromArray(array('id' => $idUsuario));
        if(!$usuario || $usuario['id_usuario_tipo'] == 2){
            $this->flashMessenger()->addWarningMessage('Usuário não encontrado!');
            return $this->redirect()->toRoute('usuario');
        }
        //Popular form
        $empresa = $this->getServiceLocator()->get('Empresa')->getRecord(1);
        $formUsuario = new alterarUsuarioForm('frmUsuario', $this->getServiceLocator(), $empresa['dominio']);
        //$formUsuario->remove('senha');
        //$formUsuario->remove('confirma_senha');
        unset($usuario['senha']);
        $formUsuario->setData($usuario);
        
        if($this->getRequest()->isPost()){
            $dados = $this->getRequest()->getPost()->toArray();
            $formUsuario->setData($dados);
            
            if($formUsuario->isValid()){
                if((empty($dados['senha']))){
                    unset($dados['senha']);
                }else{
                    $bcrypt = new Bcrypt();
                    $dados['senha'] = $bcrypt->create($dados['senha']);
                }
                $usuarioEmail = $serviceUsuario->verificaEmail($idUsuario, $dados['email']);
                if($usuarioEmail){
                    $this->flashMessenger()->addWarningMessage('Já existe usuário cadastrado para o email '.$dados['email'].'.');
                    return $this->redirect()->toRoute('usuarioAlterar', array('id' => $usuario->id));
                }else{
                    if(isset($dados['id_usuario_tipo']) && $dados['id_usuario_tipo'] == 7){
                        $this->flashMessenger()->addErrorMessage('Ocorreu algum erro, por favor tente novamente!');
                        return $this->redirect()->toRoute('usuario');
                    }
                    $serviceUsuario->updateSinc($dados, $usuario);

                    $this->flashMessenger()->addSuccessMessage('Usuario alterado com sucesso!'); 
                    if($usuario['autoprint'] != $dados['autoprint']){
                        $this->flashMessenger()->addWarningMessage('Realize logout com o usuário '.$usuario['nome'].' para a impressão automática surtir efeito!');
                    }
                    return $this->redirect()->toRoute('usuarioAlterar', array('id' => $usuario->id));
                }
            }
        }

        return new ViewModel(array(
                                'formUsuario' => $formUsuario,
                                )
                            );
    }

    public function deletarusuarioAction(){
        $serviceUsuario = $this->getServiceLocator()->get('Usuario');
        $usuario = $serviceUsuario->getRecordFromArray(array('id' => $this->params()->fromRoute('id')));
        $res = $serviceUsuario->updateSinc(array('ativo' => 'N'), $usuario);
        if($res){
           $this->flashMessenger()->addSuccessMessage('Usuário desativado com sucesso!');  
        }else{
            $this->flashMessenger()->addErrorMessage('Erro ao desativar usuário!');
        }
        return $this->redirect()->toRoute('usuario');
    }



}

