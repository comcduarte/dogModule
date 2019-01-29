<?php 

use Zend\Router\Http\Literal;
use Dog\Controller\DogController;
use Dog\Controller\Factory\DogControllerFactory;
use Dog\Controller\BreedController;
use Dog\Controller\Factory\BreedControllerFactory;
use Zend\Router\Http\Segment;
use Dog\Form\BreedForm;
use Dog\Form\Factory\BreedFormFactory;
use Dog\Form\DogForm;
use Dog\Form\Factory\DogFormFactory;
use Dog\Form\DogUsersForm;
use Dog\Form\Factory\DogUsersFormFactory;
use Dog\Controller\Factory\DogCodeControllerFactory;

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
                    'code' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/code[/:action[/:uuid]]',
                            'defaults' => [
                                'controller' => DogCodeController::class,
                                'action' => 'index',
                            ],
                        ],
                    ],
                    'dog' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/dog[/:action[/:uuid]]',
                            'defaults' => [
                                'controller' => DogController::class,
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
            'dog/code' => ['index', 'create', 'update', 'delete'],
            'dog/dog' => ['index', 'create', 'update', 'delete', 'assignuser', 'unassignuser'],
        ],
    ],
    'controllers' => [
        'factories' => [
            DogController::class => DogControllerFactory::class,
            BreedController::class => BreedControllerFactory::class,
            DogCodeController::class => DogCodeControllerFactory::class,
        ],
    ],
    'form_elements' => [
        'factories' => [
            BreedForm::class => BreedFormFactory::class,
            DogCodeForm::class => DogCodeFormFactory::class,
            DogForm::class => DogFormFactory::class,
            DogUsersForm::class => DogUsersFormFactory::class,
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
                    [
                        'label' => 'Code Maintenance',
                        'route' => 'dog/code',
                        'class' => 'dropdown-submenu',
                        'pages' => [
                            [
                                'label' => 'List Codes',
                                'route' => 'dog/code',
                            ],
                            [
                                'label' => 'Add New Code',
                                'route' => 'dog/code',
                                'action' => 'create',
                            ],
                        ],
                    ],
                    [
                        'label' => 'Dog Maintenance',
                        'route' => 'dog/dog',
                        'class' => 'dropdown-submenu',
                        'pages' => [
                            [
                                'label' => 'List Dogs',
                                'route' => 'dog/dog',
                            ],
                            [
                                'label' => 'Add New Dog',
                                'route' => 'dog/dog',
                                'action' => 'create',
                            ],
                        ],
                    ],
                ],
            ]
        ],
    ],
    'service_manager' => [
        'factories' => [
            
        ],
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