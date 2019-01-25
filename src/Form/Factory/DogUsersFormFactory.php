<?php 
namespace Dog\Form\Factory;

use Dog\Form\DogUsersForm;
use Interop\Container\ContainerInterface;

class DogUsersFormFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $adapter = $container->get('dog-model-primary-adapter');
        
        $form = new DogUsersForm();
        $form->setDbAdapter($adapter);
        $form->initialize();
        return $form;
    }
}