<?php 
namespace Dog\Form\Factory;

use Dog\Form\DogCodeForm;
use Dog\Model\DogCodeModel;
use Interop\Container\ContainerInterface;
use Midnet\Model\Uuid;

class DogCodeFormFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $uuid = new Uuid();
        
        $form = new DogCodeForm($uuid->value);
        $model = new DogCodeModel();
        $form->setInputFilter($model->getInputFilter());
        $form->init();
        return $form;
    }
}