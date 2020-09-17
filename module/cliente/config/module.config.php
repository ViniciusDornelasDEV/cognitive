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
            'alterarUsuarioCliente' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/cliente/alterar/usuario[/:id][/:usuario]',
                    'constraints' => array(
                        'id'     => '[0-9]+',
                        'usuario'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Cliente\Controller\Cliente',
                        'action'     => 'alterarusuario',
                    ),
                ),
            ),

            'alterarClienteByCliente' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/cliente/alterar/meusdados[/:usuario]',
                    'constraints' => array(
                        'usuario'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Cliente\Controller\Cliente',
                        'action'     => 'alterarcliente',
                    ),
                ),
            ),
            'alterarUsuarioClienteCliente' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/cliente/alterar/usuario/cliente[/:id][/:usuario]',
                    'constraints' => array(
                        'id'     => '[0-9]+',
                        'usuario'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Cliente\Controller\Cliente',
                        'action'     => 'alterarusuarioclientecliente',
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

            'deletarClienteUsuario' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/usuario/cliente/deletar[/:cliente][/:usuario][/:idAlterar][/:modulo]',
                    'constraints' => array(
                        'id'     => '[0-9]+',
                        'idAlterar'     => '[0-9]+',
                        'usuario'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Cliente\Controller\Cliente',
                        'action'     => 'deletarclienteusuario',
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
            'ordenarMenu' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/cliente/ordenar/menu[/:id]',
                    'constraints' => array(
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Cliente\Controller\Cliente',
                        'action'     => 'ordenarmenu',
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
            'form/cliente'              => __DIR__ . '/../view/partials/formCliente.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
);