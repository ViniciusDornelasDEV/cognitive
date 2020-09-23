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
use Cliente\Form\VincularCliente as formVincularCliente;
use Cliente\Form\Pesquisa as formPesquisa;
use Cliente\Form\AtivarUsuario as formAtivarUsuario;
use Cliente\Form\MeusDados as formMeusDados;

class ClienteController extends BaseController
{

    public function indexAction(){
      $usuario = $this->getServiceLocator()->get('session')->read();
      if($usuario['id_usuario_tipo'] == 2){
        $this->layout('layout/edicao');
      }

    	$formPesquisa = new formPesquisa('frmPesquisa');

      $params = array('ativo' => 'S');
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
          'formPesquisa'      => $formPesquisa,
          'usuario'           => $usuario
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
      $formUsuario = new formUsuario('frmUsuario', $this->getServiceLocator());
      $formVincular = new formVincularCliente('frmVincular', $this->getServiceLocator(), $idUsuario);

      //alimentar form de cliente
      $cliente = $this->getServiceLocator()->get('Cliente')->getRecord($idCliente);
      $formCliente->setData($cliente);

      //alimentar form de usuário e pesquisar usuários vinculados
      $usuario = false;
      if($idUsuario){
        $usuario = $this->getServiceLocator()->get('Usuario')->getUsuariosByCliente(array('usuario' => $idUsuario, 'cliente' => $idCliente))->current();
        $usuario['estado_br'] = $usuario['estado'];
        $formUsuario->setData($usuario);
      }

      //se veio post!
      if($this->getRequest()->isPost()){
        $dados = $this->getRequest()->getPost();
        //vincular clientee ao usuário
        if(isset($dados['cliente'])){
          $formVincular->setData($dados);
          if($formVincular->isValid()){
            $dados = $formVincular->getData();
            $this->getServiceLocator()->get('UsuarioCliente')->insert(array(
              'usuario'   =>  $idUsuario,
              'cliente'   =>  $dados['cliente']
            ));
            $this->flashMessenger()->addSuccessMessage('Cliente vinculado ao usuário com sucesso!');
            return $this->redirect()->toRoute('alterarCliente', array('id' => $idCliente, 'usuario' => $idUsuario));
          }
        }
        if(isset($dados['cargo'])){
          $formUsuario->setData($dados);
          if($formUsuario->isValid()){
            $dados = $formUsuario->getData();
            if($dados['pais'] == 'Brasil'){
              $dados['estado'] = $dados['estado_br'];
            }
            
            //validar tipos de usuário
            if($dados['id_usuario_tipo'] != 3 && $dados['id_usuario_tipo'] != 4){
              $this->flashMessenger()->addWarningMessage('Tipo de usuário inválido!');
              return $this->redirect()->toRoute('alterarCliente', array('id' => $idCliente));  
            }
            if($usuario){
              //alterar usuário
              $this->getServiceLocator()->get('Usuario')->update($dados, array('id' => $idUsuario));
              $this->flashMessenger()->addSuccessMessage('Usuário alterado com sucesso!');
              return $this->redirect()->toRoute('alterarCliente', array('id' => $idCliente, 'usuario' => $idUsuario));
            }else{
              //inserir usuário - Gerar token de ativação
              $idUsuario = $this->getServiceLocator()->get('Usuario')->getNextInsertId('tb_usuario');
              $dados['token_ativacao'] = strtolower(base64_encode(mt_rand().crypt(time().$idUsuario->Auto_increment.uniqid(mt_rand(), true))));
              $dados['ativo'] = 'A';
              $dados['cliente'] = $idCliente;

              //salvar usuário na base de dados
              $idUsuario = $this->getServiceLocator()->get('Usuario')->insert($dados);

              //vincular usuário ap cliente
              $this->getServiceLocator()->get('UsuarioCliente')->insert(array(
                'usuario'   =>  $idUsuario,
                'cliente'   =>  $idCliente
              ));
              //enviar link de ativação por email
              $mailer = $this->getServiceLocator()->get('mailer');
              $link = $this->getRequest()->getUri()->getScheme() . '://' . $this->getRequest()->getUri()->getHost().
                '/cliente/ativar/'.$dados['token_ativacao'];
              $html = $mailer->emailAtivacao($link);
              $mailer->mailUser($dados['login'], 'Cognitive, ativação de conta', $html);

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

      $usuarios = $this->getServiceLocator()->get('Usuario')->getUsuariosByCliente(array('cliente' => $idCliente));

      $clientesUsuario = false;
      if($idUsuario){
        $clientesUsuario = $this->getServiceLocator()->get('Usuario')->getClientesByUsuario(array('usuario' => $idUsuario));
      }

      return new ViewModel(array(
        'formCliente'   => $formCliente,
        'formUsuario'   => $formUsuario,
        'formVincular'  => $formVincular,
        'usuarios'      => $usuarios,
        'cliente'       => $cliente,
        'usuario'       => $usuario,
        'idUsuario'     => $idUsuario,
        'clientesUsuario' => $clientesUsuario
      ));
    }

    public function alterarusuarioAction(){
      $idUsuario = $this->params()->fromRoute('usuario');
      $idCliente = $this->params()->fromRoute('id');
      $formUsuario = new formUsuario('frmUsuario', $this->getServiceLocator());
      $formVincular = new formVincularCliente('frmVincular', $this->getServiceLocator(), $idUsuario);
      $cliente = $this->getServiceLocator()->get('Cliente')->getRecord($idCliente);

      //alimentar form de usuário e pesquisar usuários vinculados
      $usuario = false;
      if($idUsuario){
        $usuario = $this->getServiceLocator()->get('Usuario')->getUsuariosByCliente(array('usuario' => $idUsuario, 'cliente' => $idCliente))->current();
        if(!$usuario){
          $this->flashMessenger()->addSuccessMessage('Usuário não encontrado!');
          return $this->redirect()->toRoute('alterarCliente', array('id' => $idCliente));
        }
        $usuario['estado_br'] = $usuario['estado'];
        $formUsuario->setData($usuario);

      }


      if($this->getRequest()->isPost()){
        $dados = $this->getRequest()->getPost();

        //vincular clientee ao usuário
        if(isset($dados['cliente'])){
          $formVincular->setData($dados);
          if($formVincular->isValid()){
            $dados = $formVincular->getData();
            $this->getServiceLocator()->get('UsuarioCliente')->insert(array(
              'usuario'   =>  $idUsuario,
              'cliente'   =>  $dados['cliente']
            ));
            $this->flashMessenger()->addSuccessMessage('Cliente vinculado ao usuário com sucesso!');
            return $this->redirect()->toRoute('alterarUsuarioCliente', array('id' => $idCliente, 'usuario' => $idUsuario));
          }
        }
        if(isset($dados['nome'])){
          $formUsuario->setData($dados);
          if($formUsuario->isValid()){
            $dados = $formUsuario->getData();
            
            //validar tipos de usuário
            if($dados['id_usuario_tipo'] != 3 && $dados['id_usuario_tipo'] != 4){
              $this->flashMessenger()->addWarningMessage('Tipo de usuário inválido!');
              return $this->redirect()->toRoute('alterarCliente', array('id' => $idCliente));  
            }
            if($usuario){
              //alterar usuário
              $this->getServiceLocator()->get('Usuario')->update($dados, array('id' => $idUsuario));
              $this->flashMessenger()->addSuccessMessage('Usuário alterado com sucesso!');
              return $this->redirect()->toRoute('alterarCliente', array('id' => $idCliente, 'usuario' => $idUsuario));
            }else{
              //inserir usuário - Gerar token de ativação
              $idUsuario = $this->getServiceLocator()->get('Usuario')->getNextInsertId('tb_usuario');
              $dados['token_ativacao'] = strtolower(base64_encode(mt_rand().crypt(time().$idUsuario->Auto_increment.uniqid(mt_rand(), true))));
              $dados['ativo'] = 'A';
              $dados['cliente'] = $idCliente;

              //salvar usuário na base de dados
              $idUsuario = $this->getServiceLocator()->get('Usuario')->insert($dados);

              //vincular usuário ap cliente
              $this->getServiceLocator()->get('UsuarioCliente')->insert(array(
                'usuario'   =>  $idUsuario,
                'cliente'   =>  $idCliente
              ));
              //enviar link de ativação por email

              $mailer = $this->getServiceLocator()->get('mailer');
              $link = $this->getRequest()->getUri()->getScheme() . '://' . $this->getRequest()->getUri()->getHost().
                '/cliente/ativar/'.$dados['token_ativacao'];
              $html = $mailer->emailAtivacao($link);
              $mailer->mailUser($dados['login'], 'Cognitive, ativação de conta', $html);

              //gerar mensagem de sucesso e redirecionar
              $this->flashMessenger()->addSuccessMessage('Usuário inserido com sucesso!');
              return $this->redirect()->toRoute('alterarCliente', array('id' => $idCliente, 'usuario' => $idUsuario));  
            }

          }
        }
      }

      $clientesUsuario = false;
      if($idUsuario){
        $clientesUsuario = $this->getServiceLocator()->get('Usuario')->getClientesByUsuario(array('usuario' => $idUsuario));
      }

      return new ViewModel(array(
        'formUsuario'   => $formUsuario,
        'formVincular'  => $formVincular,
        'usuario'       => $usuario,
        'idUsuario'     => $idUsuario,
        'clientesUsuario' => $clientesUsuario,
        'idCliente'     => $idCliente,
        'cliente'       =>  $cliente
      ));

    }

    public function deletarclienteusuarioAction(){
      $idUsuario = $this->params()->fromRoute('usuario');
      $idCliente = $this->params()->fromRoute('cliente');
      $usuario = $this->getServiceLocator()->get('session')->read();
      if($usuario['id_usuario_tipo'] == 3){
        //verificar se usuário e cliente é do usuário logado
        $clientesAdmin = $this->getServiceLocator()->get('UsuarioCliente')->getRecords($usuario['id'], 'usuario', array('cliente'));
        $clientes = array();
        foreach ($clientesAdmin as $cliente) {
          $clientes[] = $cliente['cliente'];
        }
        if(!in_array($idCliente, $clientes)){
          $this->flashMessenger()->addWarningMessage('Cliente não encontrado!');
          return $this->redirect()->toRoute('usuarioAlterarCliente', array('id' => $idUsuario));  
        }
        $usuarioValid = $this->getServiceLocator()->get('Usuario')->getUsuariosCliente(
          array(),
          $clientes,
          $this->params()->fromRoute('usuario')
        )->current();

        if(!$usuarioValid){
          $this->flashMessenger()->addWarningMessage('Usuário ou cliente não encontrado!');
          return $this->redirect()->toRoute('usuarioAlterarCliente', array('id' => $idUsuario));  
        }
      }

      $this->getServiceLocator()->get('UsuarioCliente')->delete(array('usuario' => $idUsuario, 'cliente' => $idCliente));
      $this->flashMessenger()->addSuccessMessage('Cliente desvinculado com sucesso!');

      $modulo = $this->params()->fromRoute('modulo');
      if($usuario['id_usuario_tipo'] == 3){
        if($modulo == 'usuario'){
          return $this->redirect()->toRoute('usuarioAlterarCliente', array('id' => $idUsuario));  
        }
        return $this->redirect()->toRoute('alterarClienteByCliente');
      }
      
      if($modulo == 'usuario'){
        return $this->redirect()->toRoute('usuarioAlterar', array('id' => $idUsuario));  
      }
      return $this->redirect()->toRoute('alterarUsuarioCliente', array('id' => $this->params()->fromRoute('idAlterar'), 'usuario' => $idUsuario));
    }

    public function alterarclienteAction(){
      $this->layout('layout/cliente/admin');
      //pegar parametros da url
      $container = new Container();
      $idCliente = $container->cliente['id'];
      $idUsuario = $this->params()->fromRoute('usuario');

      //instanciar forms
      $formCliente = new formMeusDados('frmCliente');
      
      //alimentar form de cliente
      $cliente = $this->getServiceLocator()->get('Cliente')->getRecord($idCliente);
      $formCliente->setData($cliente);
      
      //se veio post!
      if($this->getRequest()->isPost()){
        $dados = $this->getRequest()->getPost();
        //alterar cliente
        $formCliente->setData($dados);
        if($formCliente->isValid()){
          //$dados = $formCliente->getData();
          $file = $this->getRequest()->getfiles()->toArray();
          if(isset($file['logo']['name']) && !empty($file['logo']['name'])){
            //fazer upload da imagem
            $logo = $this->uploadImagem($file, $idCliente);
            //alterar cliente
            $this->getServiceLocator()->get('Cliente')->update(array('logo' => $logo), array('id' => $idCliente));
            $this->flashMessenger()->addSuccessMessage('Dados alterados com sucesso!');
          }
          return $this->redirect()->toRoute('alterarClienteByCliente');

        }
      }

      $usuarios = $this->getServiceLocator()->get('Usuario')->getUsuariosByCliente(array('cliente' => $idCliente));
      return new ViewModel(array(
        'formCliente' => $formCliente,
        'usuarios'    => $usuarios,
        'cliente'     => $cliente
      ));
    }

    public function alterarusuarioclienteclienteAction(){
      $this->layout('layout/cliente/admin');
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
        $this->params()->fromRoute('usuario')
      )->current();
      if(!$usuario){
        $this->flashMessenger()->addWarningMessage('Usuário não encontrado!');
        return $this->redirect()->toRoute('usuarioCliente');
      }

      $idUsuario = $this->params()->fromRoute('usuario');
      $idCliente = $this->params()->fromRoute('id');
      $formUsuario = new formUsuario('frmUsuario', $this->getServiceLocator());
      $formVincular = new formVincularCliente('frmVincular', $this->getServiceLocator(), $idUsuario, $usuarioLogado);
      $cliente = $this->getServiceLocator()->get('Cliente')->getRecord($idCliente);

      //alimentar form de usuário e pesquisar usuários vinculados
      $usuario = false;
      if($idUsuario){
        $usuario = $this->getServiceLocator()->get('Usuario')->getUsuariosByCliente(array('usuario' => $idUsuario, 'cliente' => $idCliente))->current();
        if(!$usuario){
          $this->flashMessenger()->addSuccessMessage('Usuário não encontrado!');
          return $this->redirect()->toRoute('alterarClienteByCliente', array('id' => $idCliente));
        }
        $formUsuario->setData($usuario);

      }


      if($this->getRequest()->isPost()){
        $dados = $this->getRequest()->getPost();
        //vincular clientee ao usuário
        if(isset($dados['cliente'])){
          $formVincular->setData($dados);
          if($formVincular->isValid()){
            $dados = $formVincular->getData();
            if(!in_array($dados['cliente'], $clientes)){
              $this->flashMessenger()->addWarningMessage('Cliente não encontrado!');
              return $this->redirect()->toRoute('usuarioAlterarCliente', array('id' => $usuario['id']));  
            }
            
            $this->getServiceLocator()->get('UsuarioCliente')->insert(array(
              'usuario'   =>  $idUsuario,
              'cliente'   =>  $dados['cliente']
            ));
            $this->flashMessenger()->addSuccessMessage('Cliente vinculado ao usuário com sucesso!');
            return $this->redirect()->toRoute('alterarUsuarioClienteCliente', array('id' => $idCliente, 'usuario' => $idUsuario));
          }
        }
        if(isset($dados['nome'])){
          $formUsuario->setData($dados);
          if($formUsuario->isValid()){
            $dados = $formUsuario->getData();
            
            //validar tipos de usuário
            if($dados['id_usuario_tipo'] != 3 && $dados['id_usuario_tipo'] != 4){
              $this->flashMessenger()->addWarningMessage('Tipo de usuário inválido!');
              return $this->redirect()->toRoute('alterarClienteByCliente', array('id' => $idCliente));  
            }
            if($usuario){
              //alterar usuário
              $this->getServiceLocator()->get('Usuario')->update($dados, array('id' => $idUsuario));
              $this->flashMessenger()->addSuccessMessage('Usuário alterado com sucesso!');
              return $this->redirect()->toRoute('alterarUsuarioClienteCliente', array('id' => $idCliente, 'usuario' => $idUsuario));
            }else{
              //inserir usuário - Gerar token de ativação
              $idUsuario = $this->getServiceLocator()->get('Usuario')->getNextInsertId('tb_usuario');
              $dados['token_ativacao'] = strtolower(base64_encode(mt_rand().crypt(time().$idUsuario->Auto_increment.uniqid(mt_rand(), true))));
              $dados['ativo'] = 'A';
              $dados['cliente'] = $idCliente;

              //salvar usuário na base de dados
              $idUsuario = $this->getServiceLocator()->get('Usuario')->insert($dados);

              //vincular usuário ap cliente
              $this->getServiceLocator()->get('UsuarioCliente')->insert(array(
                'usuario'   =>  $idUsuario,
                'cliente'   =>  $idCliente
              ));
              //enviar link de ativação por email
              $mailer = $this->getServiceLocator()->get('mailer');
              $link = $this->getRequest()->getUri()->getScheme() . '://' . $this->getRequest()->getUri()->getHost().
                '/cliente/ativar/'.$dados['token_ativacao'];
              $html = $mailer->emailAtivacao($link);
              $mailer->mailUser($dados['login'], 'Cognitive, ativação de conta', $html);

              //gerar mensagem de sucesso e redirecionar
              $this->flashMessenger()->addSuccessMessage('Usuário inserido com sucesso!');
              return $this->redirect()->toRoute('alterarUsuarioClienteCliente', array('id' => $idCliente, 'usuario' => $idUsuario));  
            }

          }
        }
      }

      $clientesUsuario = false;
      if($idUsuario){
        $clientesUsuario = $this->getServiceLocator()->get('Usuario')->getClientesByUsuario(array('usuario' => $idUsuario))->toArray();
        //retirar os clientes que não são do cliente admin logado
        $usuarioLogado = $this->getServiceLocator()->get('session')->read();
        $clientesAdmin = $this->getServiceLocator()->get('UsuarioCliente')->getRecords($usuarioLogado['id'], 'usuario', array('cliente'));
        $clientesAdmin2 = array();
        foreach ($clientesAdmin as $clienteAdmin) {
          $clientesAdmin2[$clienteAdmin['cliente']] = $clienteAdmin['cliente'];
        }
        //limpar o array
        foreach ($clientesUsuario as $key => $clienteUsuario) {
          if(!in_array($clienteUsuario['id_cliente'], $clientesAdmin2)){
            unset($clientesUsuario[$key]);
          }
        }

      }

      return new ViewModel(array(
        'formUsuario'   => $formUsuario,
        'formVincular'  => $formVincular,
        'usuario'       => $usuario,
        'idUsuario'     => $idUsuario,
        'clientesUsuario' => $clientesUsuario,
        'idCliente'     => $idCliente,
        'cliente'       =>  $cliente
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
          $formUsuario = new formAtivarUsuario('frmUsuario', $this->getServiceLocator());
          $usuario['estado_br'] = $usuario['estado'];
          //se não veio post, popular form
          $formUsuario->setData(array(
            'nome'      =>  $usuario['nome'],
            'sobrenome' =>  $usuario['sobrenome'],
            'cargo'     =>  $usuario['cargo'],
            'estado' =>  $usuario['estado'],
            'telefone'  =>  $usuario['telefone'],
            'login'     =>  $usuario['login'],
          ));

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
        'formUsuario' =>  $formUsuario
      ));
    }

    public function deletarusuarioclienteAction(){
      //pegar parametros
      $idCliente = $this->params()->fromRoute('id');
      $idUsuario = $this->params()->fromRoute('usuario');
      $usuario = $this->getServiceLocator()->get('session')->read();

      //deletar usuário
      $this->getServiceLocator()->get('Usuario')->deletar(array('id' => $idUsuario));
      $this->flashMessenger()->addSuccessMessage('Usuário excluído com sucesso!');
      if($usuario['id_usuario_tipo'] == 3){
        $redir = $this->params()->fromRoute('redir');
        if($redir == 'U'){
          return $this->redirect()->toRoute('usuarioCliente');  
        }
        return $this->redirect()->toRoute('alterarClienteByCliente');
      }
      return $this->redirect()->toRoute('alterarCliente', array('id' => $idCliente));
    }

    public function inativarclienteAction(){
      $idCliente = $this->params()->fromRoute('id');
      $this->getServiceLocator()->get('Cliente')->update(array('ativo' => 'N'), array('id' => $idCliente));
      $this->flashMessenger()->addSuccessMessage('Cliente inativado com sucesso!');
      return $this->redirect()->toRoute('indexCliente');
    }

    public function selecionarclienteAction(){
      $usuario = $this->getServiceLocator()->get('session')->read();
      if($usuario['id_usuario_tipo'] == 2){
        $this->layout('layout/edicao');
      }

      if($usuario['id_usuario_tipo'] == 3){
        $this->layout('layout/cliente/admin');
      }

      if($usuario['id_usuario_tipo'] == 4){
        $this->layout('layout/cliente');
      }

      //pesquisar clientes do usuário logado
      $usuario = $this->getServiceLocator()->get('session')->read();
      if($usuario['id_usuario_tipo'] == 3 || $usuario['id_usuario_tipo'] == 4){
        $clientes = $this->getServiceLocator()->get('Usuario')->getClientesByUsuario($usuario['id']);
      }else{
        $clientes = $this->getServiceLocator()->get('Cliente')->getRecords('S', 'ativo', array('id_cliente' => 'id', 'nome_cliente' => 'nome', 'logo'), 'nome');
      }

      $idCliente = $this->params()->fromRoute('id');
      if($idCliente){
        $container = new Container();

        if($usuario['id_usuario_tipo'] == 3 || $usuario['id_usuario_tipo'] == 4){
          $cliente = $this->getServiceLocator()->get('Usuario')->getClientesByUsuario($usuario['id'], $idCliente)->current();
          $cliente = array(
            'id'            =>  $cliente['id_cliente'],
            'nome'          =>  $cliente['nome_cliente'],
            'logo'          =>  $cliente['logo'],
            'id_azure'      =>  $cliente['id_azure'],
            'usuario_azure' =>  $cliente['usuario_azure'],
            'senha_azure'   =>  $cliente['senha_azure'],
            'ativo'         =>  $cliente['cliente_ativo']
          );
        }else{
          $cliente = $this->getServiceLocator()->get('Cliente')->getRecordFromArray(array(
            'id'    => $this->params()->fromRoute('id'),
            'ativo' => 'S'
          ));
        }

        if($cliente){
          $container->cliente = $cliente;
          //redir para dash do cliente, se não existir, gerar msg de alerta
          $dashBoards = $this->getServiceLocator()->get('Dashboard')->getDashboardsByParams(array('cliente' => $cliente['id']), true); 
          if($dashBoards->count() > 0){
            $dashBoard = $dashBoards->current();
            return $this->redirect()->toRoute('visualizarDashboard', array('id' => $dashBoard['id']));
          }else{
            $this->flashMessenger()->addWarningMessage('Não existe nenhuma dashboard ativa para o cliente '.$cliente['nome']);
            return $this->redirect()->toRoute('selecionarCliente');
          }
        }else{
          $this->flashMessenger()->addWarningMessage('Cliente não encontrado ou inativo!');
          return $this->redirect()->toRoute('selecionarCliente');
        }
      }
      
      $container = new Container();
      return new ViewModel(array(
        'clientes'            =>  $clientes,
        'clienteSelecionado'  =>  $container->cliente
      ));
    }

    public function ordenarmenuAction(){

      if($this->getRequest()->isPost()){
        $dados = $this->getRequest()->getPost();
        $ordem = json_decode($dados['ordem'], true);
        
        //salvar nova ordem do menu
        $status = $this->getServiceLocator()->get('Dashboard')->salvarMenu($ordem);
        if($status == true){
          $this->flashMessenger()->addSuccessMessage('Menu ordenado com sucesso!');
        }else{
          $this->flashMessenger()->addErrorMessage('Ocorreu algum erro ao ordenar menu, por favor tente novamente!');
        }
        return $this->redirect()->toRoute('ordenarMenu', array('id' => $this->params()->fromRoute('id')));
      }

      $container = new Container();
      $cliente = $this->getServiceLocator()->get('Cliente')->getRecord($this->params()->fromRoute('id'));
      $menuDashboards = $this->getServiceLocator()->get('Dashboard')->getMenu($cliente['id']);

      return new ViewModel(array(
        'menuDashboards' =>  $menuDashboards,
        'cliente'        =>  $cliente
      ));
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