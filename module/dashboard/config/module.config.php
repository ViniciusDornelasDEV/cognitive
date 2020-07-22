<?php
return array(
    'router' => array(
        'routes' => array(
            'indexDashboard' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/dashboards/listar[/:page]',
                    'defaults' => array(
                        'controller' => 'Dashboard\Controller\Dashboard',
                        'action'     => 'index',
                    ),
                ),
            ),
            'novoDashboard' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/dashboards/novo',
                    'defaults' => array(
                        'controller' => 'Dashboard\Controller\Dashboard',
                        'action'     => 'novo',
                    ),
                ),
            ),
            'alterarDashboard' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/dashboards/alterar[/:id]',
                    'constraints' => array(
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Dashboard\Controller\Dashboard',
                        'action'     => 'alterar',
                    ),
                ),
            ),
            'visualizarDashboard' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/dashboards/visualizar[/:id]',
                    'constraints' => array(
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Dashboard\Controller\Dashboard',
                        'action'     => 'visualizardashboard',
                    ),
                ),
            ),
        ),
    ),
	'controllers' => array(
        'invokables' => array(
            'Dashboard\Controller\Dashboard'    => 'Dashboard\Controller\DashboardController',
        ),
    ),
    'view_manager' => array(
        'template_map' => array(
            
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
);