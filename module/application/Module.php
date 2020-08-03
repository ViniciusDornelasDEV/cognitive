<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;

use Zend\Authentication\Storage\Session;
use Zend\Authentication\AuthenticationService;
use Zend\Db\TableGateway\TableGateway;
use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Role\GenericRole as Role;
use Zend\Permissions\Acl\Resource\GenericResource as Resource;
use Application\Factory;
use Application\Model\BaseTable;
use Zend\Session\Container;

use Service\ErrorHandling as ErrorHandlingService;
use Zend\Log\Logger;
use Zend\Log\Writer\Stream as LogWriterStream;

use Rest\Classes\JwtAdapter;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {   
        //se veio HTTP redir p/ HTTPS
        /*if($_SERVER['SERVER_PORT'] != '443') {
            header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
            exit();
        }*/

        ini_set('date.timezone', "America/Sao_Paulo");
        //ini_set('date.timezone', "America/Belem");
        //Config app e service manager
        $this->app = $e->getApplication();
        $this->serviceManager = $this->app->getServiceManager();
        //Pegar rota atual
        $router = $this->serviceManager->get('router');
        $request = $this->serviceManager->get('request');
        $routeMatch = $router->match($request);


        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);

        //caso haja algum erro não renderizar o layout(ele tem o menu!)
        $eventManager->attach('dispatch.error', function($event){
            $exception = $event->getResult()->exception;
            /*if ($exception) {
                $service = $this->serviceManager->get('ExceptionLog')->logException($exception);
            }*/
        });

        //capturar rota
        $rota = $routeMatch->getMatchedRouteName();
        
        $eventManager->attach(MvcEvent::EVENT_DISPATCH_ERROR, array($this, 'handleError'));
        $eventManager->attach(MvcEvent::EVENT_RENDER_ERROR, array($this, 'handleError'));

        //set up some rules here 
        $viewHelperManager = $e->getApplication()->getServiceManager()->get('ViewHelperManager');
        $pluralHelper = $viewHelperManager->get('Plural');
        // Here is the rule for English
        $pluralHelper->setPluralRule('nplurals=2; plural=(n==1 ? 0 : 1)');

        
        if($routeMatch){
            //Verifica acessos
            $session = $this->serviceManager->get('session');
            $usuario = $this->serviceManager->get('Usuario');
            
            if(!$this->verificaAcesso($session, $usuario, $rota)){
                $this->dispatchToLogout($e);
            }
        }

        //passar cliente para o layout
        $container = new Container();
        if(isset($container->cliente)){
          $viewModel = $e->getApplication()->getMvcEvent()->getViewModel();
          $viewModel->clienteSelecionado = $container->cliente;

          //passar menu para o layout
          $viewModel->menuDashboards = $this->serviceManager->get('Dashboard')->getMenu($container->cliente['id']);
        }
        
        if($session->read()){
          $viewModel->usuario = $session->read();
        }
        
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array( 
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                    'Application\Validator'
                ),
            ),
        );
    }

    public function getServiceConfig() {
        return array(
            'invokables' => array(
                // defining it as invokable here, any factory will do too
                'my_image_service' => 'Imagine\Gd\Imagine',
            ),
            'factories' => array(
                /* DATABASE ADAPTER OBJECTS */
                'db_adapter_factory' => function($sm) {
                    return new Factory\MyAdapterFactory($sm);
                },
                'db_adapter_main' => function($sm) {
                    $factory = $sm->get('db_adapter_factory');
                    $factory->setConfigKey();
                    
                    return $factory->createService();
                },
                'Mailer' => function() {

                    $from = array(
                        'name' => 'Cognitive',
                        'email' => 'contato@sistemacognitive.tk',
                        'contact_details' => array('rua' => 'Paraopeba 610'));

                    $mailer = new Service\Mailer($from);
                    $mailer->setContactDetails($from['contact_details']);

                    return $mailer;
                },

                'ExceptionLog' => function() {
                    return new Service\ExceptionLog();
                },

                
                'session' => function ($sm) {
                    $config = $sm->get('config');
                    if (isset($config['session'])) {
                        $session = $config['session']['config']['options']['name'];
                        
                        //Various Session options
                        $manager = new \Zend\Session\SessionManager();                        
                        
                         if(filter_input(INPUT_SERVER, 'APPLICATION_ENV') === 'production'){
                             
                            $manager->getConfig()
                                    ->setCookieHttpOnly(true)
                                    ->setCookieSecure(false);
                            $manager->start();

                        }
                        
                        return new Session($session);
                    }
                },
                'Pais' => function($sm) {
                    $tableGateway = new TableGateway('tb_pais', $sm->get('db_adapter_main'));
                    $updates = new BaseTable($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },           
                'Estado' => function($sm) {
                    $tableGateway = new TableGateway('tb_estado', $sm->get('db_adapter_main'));
                    $updates = new BaseTable($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },
            ),
        );
    }

    public function verificaAcesso($session, $usuario, $rota = 'home') { 
        $rotasPublicas = array('logout', 'login', 'recuperarSenha', 'ativarUsuarioCliente', 'ativarUsuario', 'tokenRecuperar',
          'loginGoogle');
         if(in_array($rota, $rotasPublicas)) {
            return true;
        }

        //verificar se usuário está logado (caso não esteja redir para login)
        $auth = new AuthenticationService;
        $auth->setStorage($session);
        if(!$auth->hasIdentity()){
            //redir para login 
            return false;
        }
        $user = $auth->getIdentity();
        $user = $usuario->getUserData(array('tb_usuario.id' => $user['id']));

        //verificar se usuário tem permissão para acessar página (caso não tenha redir para logout)
        $container = new Container();
        if($container->acl->isAllowed($container->acl->getRole($user->perfil), $rota, $rota)){
            return true;
        }else{
            return false;
        }
    }

    protected function dispatchToLogout($event) {
        $url = $event->getRouter()->assemble(array('controller' => 'Usuario\Controller\Usuario'), array('name' => 'logout'));
        $response = $event->getResponse();
        $response->getHeaders()->addHeaderLine('Location', $url);
        $response->setStatusCode(302);
        $response->sendHeaders();
    }

    public function handleError(MvcEvent $event) {
        $result = $event->getResult(); 
        $result->setTerminal(true);
    }
}
