<?php 
namespace Dog\Form\Factory;

use Interop\Container\ContainerInterface;
use Dog\Form\DogForm;
use Dog\Model\DogModel;

class DogFormFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $form = new DogForm();
        $model = new DogModel();
        $form->setInputFilter($model->getInputFilter());
        $form->init();
        return $form;
    }
}