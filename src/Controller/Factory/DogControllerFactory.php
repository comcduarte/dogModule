<?php 
namespace Dog\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Dog\Controller\DogController;
use Dog\Form\DogForm;

class DogControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $controller = new DogController();
        $controller->setDbAdapter($container->get('dog-model-primary-adapter'));
        $controller->form = $container->get('FormElementManager')->get(DogForm::class);
        return $controller;
    }
}