<?php 
namespace Dog\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Dog\Controller\OwnerController;

class OwnerControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $controller = new OwnerController();
        $controller->setDbAdapter($container->get('dog-model-primary-adapter'));
        return $controller;
    }
}