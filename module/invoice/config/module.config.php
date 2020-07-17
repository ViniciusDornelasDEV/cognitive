<?php
return array(
    'router' => array(
        'routes' => array(
            'indexInvoice' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/invoice/listar[/:page]',
                    'defaults' => array(
                        'controller' => 'Invoice\Controller\Invoice',
                        'action'     => 'index',
                    ),
                ),
            ),
            'novoInvoice' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/invoice/novo',
                    'defaults' => array(
                        'controller' => 'Invoice\Controller\Invoice',
                        'action'     => 'novo',
                    ),
                ),
            ),
            'alterarInvoice' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/invoice/alterar[/:id]',
                    'constraints' => array(
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Invoice\Controller\Invoice',
                        'action'     => 'alterar',
                    ),
                ),
            ),
        ),
    ),
	'controllers' => array(
        'invokables' => array(
            'Invoice\Controller\Invoice'    => 'Invoice\Controller\InvoiceController',
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