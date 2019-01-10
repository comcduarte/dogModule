<?php 

use Zend\Router\Http\Literal;
use Dog\Controller\DogController;
use Dog\Controller\Factory\DogControllerFactory;
use Dog\Controller\BreedController;
use Dog\Controller\Factory\BreedControllerFactory;
use Zend\Router\Http\Segment;
use Dog\Form\BreedForm;
use Dog\Form\Factory\BreedFormFactory;

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
                'may_terminate' => true,
                'child_routes' => [
                    'breed' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/breed[/:action[/:uuid]]',
                            'defaults' => [
                                'controller' => BreedController::class,
                                'action' => 'index',
                            ],
                        ],
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
            'dog/breed' => ['index', 'create', 'update', 'delete'],
        ],
    ],
    'controllers' => [
        'factories' => [
            DogController::class => DogControllerFactory::class,
            BreedController::class => BreedControllerFactory::class,
        ],
    ],
    'form_elements' => [
        'factories' => [
            BreedForm::class => BreedFormFactory::class,
        ],
    ],
    'navigation' => [
        'default' => [
            [
                'label' => 'Dog',
                'route' => 'dog',
                'class' => 'dropdown',
                'pages' => [
                    [
                        'label' => 'Breed Maintenance',
                        'route' => 'dog/breed',
                        'class' => 'dropdown-submenu',
                        'pages' => [
                            [
                                'label' => 'List Breeds',
                                'route' => 'dog/breed',
                            ],
                            [
                                'label' => 'Add New Breed',
                                'route' => 'dog/breed',
                                'action' => 'create',
                            ],
                        ],
                    ],
                ],
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