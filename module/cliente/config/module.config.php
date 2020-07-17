<?php
return array(
    'router' => array(
        'routes' => array(
            'indexCliente' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/listar/clientes[/:page]',
                    'defaults' => array(
                        'controller' => 'Cliente\Controller\Cliente',
                        'action'     => 'index',
                    ),
                ),
            ),
            'novoCliente' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/cliente/novo',
                    'defaults' => array(
                        'controller' => 'Cliente\Controller\Cliente',
                        'action'     => 'novo',
                    ),
                ),
            ),
            'alterarCliente' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/cliente/alterar[/:id][/:usuario]',
                    'constraints' => array(
                        'id'     => '[0-9]+',
                        'usuario'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Cliente\Controller\Cliente',
                        'action'     => 'alterar',
                    ),
                ),
            ),
        ),
    ),
	'controllers' => array(
        'invokables' => array(
            'Cliente\Controller\Cliente'    => 'Cliente\Controller\ClienteController',
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