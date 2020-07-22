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
            'ativarUsuarioCliente' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/cliente/ativar[/:token]',
                    'defaults' => array(
                        'controller' => 'Cliente\Controller\Cliente',
                        'action'     => 'ativarusuariocliente',
                    ),
                ),
            ),
            'deletarUsuarioCliente' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/cliente/usuario/deletar[/:id][/:usuario]',
                    'constraints' => array(
                        'id'     => '[0-9]+',
                        'usuario'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Cliente\Controller\Cliente',
                        'action'     => 'deletarusuariocliente',
                    ),
                ),
            ),

            'inativarCliente' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/cliente/deletar[/:id]',
                    'constraints' => array(
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Cliente\Controller\Cliente',
                        'action'     => 'inativarcliente',
                    ),
                ),
            ),

            'selecionarCliente' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/cliente/selecionar[/:id]',
                    'constraints' => array(
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Cliente\Controller\Cliente',
                        'action'     => 'selecionarcliente',
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