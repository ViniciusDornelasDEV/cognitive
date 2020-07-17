<?php
namespace Usuario;

use Zend\Db\TableGateway\TableGateway;
use Application\Model\BaseTable;
use Zend\Session\Container;

class Module
{
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
                ),
            ),
        );
    }

    // Add this method:
    public function getServiceConfig() {
        return array(
            'factories' => array(
                /* My Tables  */
                'Usuario' => function($sm) {
                    $tableGateway = new TableGateway('tb_usuario', $sm->get('db_adapter_main'));
                    return new Model\Usuario($tableGateway);
                },
                'UsuarioTipo' => function($sm) {
                    $tableGateway = new TableGateway('tb_usuario_tipo', $sm->get('db_adapter_main'));
                    $updates = new BaseTable($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },
                'UsuarioRecurso' => function($sm) {
                    $tableGateway = new TableGateway('tb_usuario_recurso', $sm->get('db_adapter_main'));
                    $updates = new Model\UsuarioRecurso($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },
                'Recurso' => function($sm) {
                    $tableGateway = new TableGateway('tb_recurso', $sm->get('db_adapter_main'));
                    $updates = new Model\Recurso($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },
                'Modulo' => function($sm) {
                    $tableGateway = new TableGateway('tb_recurso_modulo', $sm->get('db_adapter_main'));
                    $updates = new BaseTable($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },
            ),
            'invokables' => array(
                'ImageService' => 'Imagine\Gd\Imagine',
            ),
        );
    }
}
