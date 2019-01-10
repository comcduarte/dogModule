<?php 
namespace Dog\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Dog\Controller\BreedController;
use Dog\Form\BreedForm;

class BreedControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $controller = new BreedController();
        $controller->setDbAdapter($container->get('dog-model-primary-adapter'));
        $controller->form = $container->get('FormElementManager')->get(BreedForm::class);
        return $controller;
    }
}