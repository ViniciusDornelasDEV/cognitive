<?php
namespace Dashboard;

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
                /*'HorarioFuncionamento' => function($sm) {
                    $tableGateway = new TableGateway('tb_horario_funcionamento', $sm->get('db_adapter_main'));
                    $updates = new Model\HorarioFuncionamento($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },
                'Recesso' => function($sm) {
                    $tableGateway = new TableGateway('tb_recesso', $sm->get('db_adapter_main'));
                    $updates = new BaseTable($tableGateway);
                    $updates->setServiceLocator($sm);
                    return $updates;
                },*/
            ),
        );
    }
}
