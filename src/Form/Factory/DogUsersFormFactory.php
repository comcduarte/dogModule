<?php 
namespace Dog\Form\Factory;

use Dog\Form\DogUsersForm;
use Interop\Container\ContainerInterface;
use Midnet\Model\Uuid;

class DogUsersFormFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $uuid = new Uuid();
        $adapter = $container->get('dog-model-primary-adapter');
        
        $form = new DogUsersForm($uuid->value);
        $form->setDbAdapter($adapter);
        $form->initialize();
        return $form;
    }
}