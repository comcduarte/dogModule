<?php 
namespace Dog\Controller\Factory;

use Dog\Controller\DogCodeController;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Dog\Form\DogCodeForm;

class DogCodeControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $controller = new DogCodeController();
        $controller->setDbAdapter($container->get('dog-model-primary-adapter'));
        $controller->form = $container->get('FormElementManager')->get(DogCodeForm::class);
        return $controller;
    }
}