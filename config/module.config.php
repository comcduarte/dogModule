<?php 
namespace Dog;

use Dog\Controller\BreedController;
use Dog\Controller\DogCodeController;
use Dog\Controller\DogController;
use Dog\Controller\LicenseController;
use Dog\Controller\Factory\BreedControllerFactory;
use Dog\Controller\Factory\DogCodeControllerFactory;
use Dog\Controller\Factory\DogControllerFactory;
use Dog\Controller\Factory\LicenseControllerFactory;
use Dog\Form\BreedForm;
use Dog\Form\DogCodeForm;
use Dog\Form\DogForm;
use Dog\Form\DogUsersForm;
use Dog\Form\LicenseForm;
use Dog\Form\Factory\BreedFormFactory;
use Dog\Form\Factory\DogCodeFormFactory;
use Dog\Form\Factory\DogFormFactory;
use Dog\Form\Factory\DogUsersFormFactory;
use Dog\Form\Factory\LicenseFormFactory;
use Zend\Router\Http\Literal;
use Zend\Router\Http\Segment;
use Dog\Controller\Factory\OwnerControllerFactory;

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
                    'license' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/license[/:action[/:uuid]]',
                            'defaults' => [
                                'controller' => LicenseController::class,
                                'action' => 'index',
                            ],
                        ],
                    ],
                    'owner' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/owner[/:action[/:uuid]]',
                            'defaults' => [
                                'controller' => OwnerController::class,
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
            'dog/dog' => ['index', 'create', 'update', 'delete', 'assignuser', 'unassignuser', 'import'],
            'dog/license' => ['index', 'create', 'update', 'delete', 'assigncode','unassigncode', 'license'],
            'dog/owner' => ['index', 'create', 'update', 'delete', 'find'],
        ],
    ],
    'controllers' => [
        'factories' => [
            DogController::class => DogControllerFactory::class,
            BreedController::class => BreedControllerFactory::class,
            DogCodeController::class => DogCodeControllerFactory::class,
            LicenseController::class => LicenseControllerFactory::class,
            OwnerController::class => OwnerControllerFactory::class,
        ],
    ],
    'form_elements' => [
        'factories' => [
            BreedForm::class => BreedFormFactory::class,
            DogCodeForm::class => DogCodeFormFactory::class,
            DogForm::class => DogFormFactory::class,
            DogUsersForm::class => DogUsersFormFactory::class,
            LicenseForm::class => LicenseFormFactory::class,
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
                    [
                        'label' => 'License Maintenance',
                        'route' => 'dog/license',
                        'class' => 'dropdown-submenu',
                        'pages' => [
                            [
                                'label' => 'List Licenses',
                                'route' => 'dog/license',
                            ],
                            [
                                'label' => 'Add New License',
                                'route' => 'dog/license',
                                'action' => 'create',
                            ],
                        ],
                    ],
                    [
                        'label' => 'Owner Maintenance',
                        'route' => 'dog/owner',
                        'class' => 'dropdown-submenu',
                        'pages' => [
                            [
                                'label' => 'List Owners',
                                'route' => 'dog/owner',
                            ],
                            [
                                'label' => 'Add New Owner',
                                'route' => 'dog/owner',
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
        'template_map' => [
            'layout/license' => __DIR__ . '/../view/layout/license.phtml',
        ],
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
];