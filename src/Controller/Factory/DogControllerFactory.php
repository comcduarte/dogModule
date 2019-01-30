<?php 
namespace Dog\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Dog\Controller\DogController;
use Dog\Form\DogForm;
use Dog\Form\DogUsersForm;
use Dog\Form\LicenseForm;

class DogControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $controller = new DogController();
        $controller->setDbAdapter($container->get('dog-model-primary-adapter'));
        $controller->form = $container->get('FormElementManager')->get(DogForm::class);
        $controller->DogUsersForm = $container->get('FormElementManager')->get(DogUsersForm::class);
        $controller->licenseForm = $container->get('FormElementManager')->get(LicenseForm::class);
        return $controller;
    }
}