<?php
return array(
    'router' => array(
        'routes' => array(
            //Login
            'login' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/login',
                    'defaults' => array(
                        'controller' => 'Usuario\Controller\Usuario',
                        'action'     => 'login',
                    ),
                ),
            ),
            'loginGoogle' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/login/google',
                    'defaults' => array(
                        'controller' => 'Usuario\Controller\Usuario',
                        'action'     => 'logingoogle',
                    ),
                ),
            ),
            'loginMicrosoft' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/login/microsoft[/:acao]',
                    'defaults' => array(
                        'controller' => 'Usuario\Controller\Usuario',
                        'action'     => 'loginmicrosoft',
                    ),
                ),
            ),

            'logout' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/logout[/:sigla]',
                    'defaults' => array(
                        'controller' => 'Usuario\Controller\Usuario',
                        'action'     => 'logout',
                    ),
                ),
            ),
            //listar usuários
            'usuario' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/usuario[/:page][/:tipo]',
                    'defaults' => array(
                        'controller' => 'Usuario\Controller\Usuario',
                        'action'     => 'index',
                    ),
                ),
            ),
            'usuarioCliente' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/usuario/cliente[/:page]',
                    'defaults' => array(
                        'controller' => 'Usuario\Controller\Usuario',
                        'action'     => 'indexcliente',
                    ),
                ),
            ),
            //Novo usuario
            'usuarioNovo' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/usuario/novo',
                    'defaults' => array(
                        'controller' => 'Usuario\Controller\Usuario',
                        'action'     => 'novo',
                    ),
                ),
            ),
            'usuarioNovoCliente' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/usuario/novo/cliente',
                    'defaults' => array(
                        'controller' => 'Usuario\Controller\Usuario',
                        'action'     => 'novocliente',
                    ),
                ),
            ),
            'ativarUsuario' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/usuario/ativar[/:token]',
                    'defaults' => array(
                        'controller' => 'Usuario\Controller\Usuario',
                        'action'     => 'ativarusuario',
                    ),
                ),
            ),
            //Alterar usuario
            'usuarioAlterar' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/usuario/alterar[/:id]',
                    'constraints' => array(
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Usuario\Controller\Usuario',
                        'action'     => 'alterar',
                    ),
                ),
            ),
            'usuarioAlterarCliente' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/usuario/alterar/cliente[/:id]',
                    'constraints' => array(
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Usuario\Controller\Usuario',
                        'action'     => 'alterarcliente',
                    ),
                ),
            ),
            //Deletar usuario
            'usuarioDeletar' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/usuario/deletarusuario[/:id]',
                    'constraints' => array(
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Usuario\Controller\Usuario',
                        'action'     => 'deletarusuario',
                    ),
                ),
            ),
            'usuarioDeletarCliente' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/usuario/deletarusuario/cliente[/:id]',
                    'constraints' => array(
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Usuario\Controller\Usuario',
                        'action'     => 'deletarusuariocliente',
                    ),
                ),
            ),


            'tipousuario' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/tipousuario[/:page]',
                    'defaults' => array(
                        'controller' => 'Usuario\Controller\Tipousuario',
                        'action'     => 'index',
                    ),
                ),
            ),
            //Novo tipousuario
            'tipousuarioNovo' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/tipousuario/novo[/:id]',
                    'constraints' => array(
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Usuario\Controller\Tipousuario',
                        'action'     => 'novo',
                    ),
                ),
            ),
            //Alterar tipousuario
            'tipousuarioAlterar' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/tipousuario/alterar[/:id][/:recurso]',
                    'constraints' => array(
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Usuario\Controller\Tipousuario',
                        'action'     => 'alterar',
                    ),
                ),
            ),
            //Deletar tipousuario
            'tipousuarioDeletar' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/tipousuario/deletartipousuario[/:id]',
                    'constraints' => array(
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Usuario\Controller\Tipousuario',
                        'action'     => 'deletartipousuario',
                    ),
                ),
            ),

            //Desvincular recurso
            'recursoDeletar' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/tipousuario/deletarrecurso[/:id][/:tipousuario]',
                    'constraints' => array(
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Usuario\Controller\Tipousuario',
                        'action'     => 'deletarrecurso',
                    ),
                ),
            ),
            //Alterar senha
            'alterarSenha' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/alterarsenha',
                    'defaults' => array(
                        'controller' => 'Usuario\Controller\Usuario',
                        'action'     => 'alterarsenha',
                    ),
                ),
            ),
            'recuperarSenha' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/recuperarsenha',
                    'defaults' => array(
                        'controller' => 'Usuario\Controller\Usuario',
                        'action'     => 'recuperarsenha',
                    ),
                ),
            ),
            'tokenRecuperar' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/usuario/recuperarsenha/token[/:token]',
                    'defaults' => array(
                        'controller' => 'Usuario\Controller\Usuario',
                        'action'     => 'tokenrecuperar',
                    ),
                ),
            ),

            'descricaoRecurso' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/descricaorecurso',
                    'defaults' => array(
                        'controller' => 'Usuario\Controller\Tipousuario',
                        'action'     => 'descricaorecurso',
                    ),
                ),
            ),

            'moduloRecurso' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/recursos',
                    'defaults' => array(
                        'controller' => 'Usuario\Controller\Tipousuario',
                        'action'     => 'modulo',
                    ),
                ),
            ),

            'salvarTemplate' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/template/salvar',
                    'defaults' => array(
                        'controller' => 'Usuario\Controller\Usuario',
                        'action'     => 'salvartemplate',
                    ),
                ),
            ),
            'salvarMenuHidden' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/menu/hidden/salvar',
                    'defaults' => array(
                        'controller' => 'Usuario\Controller\Usuario',
                        'action'     => 'salvarmenu',
                    ),
                ),
            ),
        ),
    ),
	'controllers' => array(
        'invokables' => array(
            'Usuario\Controller\Usuario' => 'Usuario\Controller\UsuarioController',
            'Usuario\Controller\Tipousuario' => 'Usuario\Controller\TipousuarioController'
        ),
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
        'template_map' => array(
            'form/login'        => __DIR__ . '/../view/partials/formLogin.phtml',
            'form/recuperaSenha'        => __DIR__ . '/../view/partials/formRecuperaSenha.phtml',
            'layout/login'           => __DIR__ . '/../view/layout/layoutlogin.phtml'
        ),
    ),
);