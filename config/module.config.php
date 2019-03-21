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
use Dog\Controller\ReportController;
use Dog\Controller\Factory\ReportControllerFactory;
use Dog\Controller\Factory\ConfigControllerFactory;
use Dog\Controller\ImageController;
use Dog\Controller\Factory\ImageControllerFactory;

return [
    'router' => [
        'routes' => [
            'image' => [
                'type' => Segment::class,
                'priority' => 10,
                'options' => [
                    'route' => '/image/:uuid',
                    'defaults' => [
                        'controller' => ImageController::class,
                        'action' => 'display',
                    ],
                ],
            ],
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
                            'route' => '/breed/[:action/[id/:uuid]][page/:page][/:count]',
                            'defaults' => [
                                'controller' => BreedController::class,
                            ],
                            'constraints' => [
                                'page' => '[0-9]+',
                                'uuid' => '[a-f0-9-]+',
                                'count' => '[0-9]+',
                            ],
                        ],
                    ],
                    'code' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/code/[:action/[id/:uuid]][page/:page][/:count]',
                            'defaults' => [
                                'controller' => DogCodeController::class,
                            ],
                            'constraints' => [
                                'page' => '[0-9]+',
                                'uuid' => '[a-f0-9-]+',
                                'count' => '[0-9]+',
                            ],
                        ],
                    ],
                    'config' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/config/[:action/[id/:uuid]][page/:page][/:count]',
                            'defaults' => [
                                'controller' => ConfigController::class,
                            ],
                            'constraints' => [
                                'page' => '[0-9]+',
                                'uuid' => '[a-f0-9-]+',
                                'count' => '[0-9]+',
                            ],
                        ],
                    ],
                    'dog' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/dog/[:action/[id/:uuid]][page/:page][/:count]',
                            'defaults' => [
                                'controller' => DogController::class,
                            ],
                            'constraints' => [
                                'page' => '[0-9]+',
                                'uuid' => '[a-f0-9-]+',
                                'count' => '[0-9]+',
                            ],
                        ],
                    ],
                    'license' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/license/[:action/[id/:uuid]][page/:page][/:count]',
                            'defaults' => [
                                'controller' => LicenseController::class,
                            ],
                            'constraints' => [
                                'page' => '[0-9]+',
                                'uuid' => '[a-f0-9-]+',
                                'count' => '[0-9]+',
                            ],
                        ],
                    ],
                    'owner' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/owner/[:action/[id/:uuid]][page/:page][/:count]',
                            'defaults' => [
                                'controller' => OwnerController::class,
                            ],
                            'constraints' => [
                                'page' => '[0-9]+',
                                'uuid' => '[a-f0-9-]+',
                                'count' => '[0-9]+',
                            ],
                        ],
                    ],
                    'report' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/report/[:action/[id/:uuid]][page/:page][/:count]',
                            'defaults' => [
                                'controller' => ReportController::class,
                            ],
                            'constraints' => [
                                'page' => '[0-9]+',
                                'uuid' => '[a-f0-9-]+',
                                'count' => '[0-9]+',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'acl' => [
        'guest' => [
            'dog/config' => ['index', 'import','clear'],
        ],
        'member' => [
            'dog' => ['index'],
            'dog/breed' => ['index', 'create', 'update', 'delete'],
            'dog/code' => ['index', 'create', 'update', 'delete'],
            'dog/config' => ['index', 'create', 'update', 'delete', 'import', 'clear', 'breedimport'],
            'dog/dog' => ['index', 'create', 'update', 'delete', 'assignuser', 'unassignuser', 'find', 'import'],
            'dog/license' => ['index', 'create', 'update', 'delete', 'assigncode','unassigncode', 'license', 'find'],
            'dog/owner' => ['index', 'create', 'update', 'delete', 'find'],
            'dog/report' => ['index', 'create', 'update', 'delete', 'view'],
            'image' => ['display'],
        ],
    ],
    'controllers' => [
        'factories' => [
            DogController::class => DogControllerFactory::class,
            BreedController::class => BreedControllerFactory::class,
            ConfigController::class => ConfigControllerFactory::class,
            DogCodeController::class => DogCodeControllerFactory::class,
            LicenseController::class => LicenseControllerFactory::class,
            OwnerController::class => OwnerControllerFactory::class,
            ReportController::class => ReportControllerFactory::class,
            ImageController::class => ImageControllerFactory::class,
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
            ],
            [
                'label' => 'Reports',
                'route' => 'dog/report',
                'class' => 'dropdown',
                'pages' => [
                    [
                        'label' => 'Available Reports',
                        'route' => 'dog/report',
                    ],
                    [
                        'label' => 'Add New Report',
                        'route' => 'dog/report',
                        'action' => 'create',
                    ],
                ],
            ],
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