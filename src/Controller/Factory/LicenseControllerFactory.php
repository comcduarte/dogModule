<?php 
namespace Dog\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Dog\Form\LicenseForm;
use Dog\Controller\LicenseController;

class LicenseControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $controller = new LicenseController();
        $controller->setDbAdapter($container->get('dog-model-primary-adapter'));
        $form = new LicenseForm();
        $form->initialize();
        $controller->form = $form;
        
        return $controller;
    }
}