<?php
namespace Cliente;

use Zend\Db\TableGateway\TableGateway;
use Application\Model\BaseTable;

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

    public function getServiceConfig() {
        return array(
            'factories' => array(
                'Cliente' => function($sm) {
                    $tableGateway = new TableGateway('tb_cliente', $sm->get('db_adapter_main'));
                    $updates = new Model\Cliente($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },
            ),
        );
    }
}
