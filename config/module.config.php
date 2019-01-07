<?php 

use Zend\Router\Http\Literal;
use Dog\Controller\DogController;
use Dog\Controller\Factory\DogControllerFactory;

return [
    'router' => [
        'routes' => [
            'dog' => [
                'type' => Literal::class,
                'priority' => 1,
                'options' => [
                    'route' => '/dog',
                    'defaults' => [
                        'controller' => DogController::class,
                        'action' => 'index',
                    ],
                ],
            ],
        ],
    ],
    'acl' => [
        'guest' => [
            'dog' => ['index'],
        ],
        'member' => [
            'dog' => ['index'],
        ],
    ],
    'controllers' => [
        'factories' => [
            DogController::class => DogControllerFactory::class,
        ],
    ],
    'navigation' => [
        'default' => [
            [
                'label' => 'Dog',
                'route' => 'dog',
            ]
        ],
    ],
    'service_manager' => [
        'aliases' => [
            'dog-model-primary-adapter-config' => 'user-model-primary-adapter-config',
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
];