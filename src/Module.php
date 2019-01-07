<?php
namespace Dog;

use Zend\Db\Adapter\Adapter;

class Module
{

    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }

    public function getServiceConfig()
    {
        return [
            'factories' => [
                'dog-model-primary-adapter' => function ($container) {
                    return new Adapter($container->get('dog-model-primary-adapter-config'));
                }
            ]
        ];
    }
}