<?php 
namespace Dog\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Dog\Controller\ReportController;

class ReportControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $controller = new ReportController();
        $controller->setDbAdapter($container->get('dog-model-primary-adapter'));
        return $controller;
    }
}
