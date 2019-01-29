<?php 
namespace Dog\Form\Factory;

use Dog\Form\DogCodeForm;
use Dog\Model\DogCodeModel;
use Interop\Container\ContainerInterface;

class DogCodeFormFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $form = new DogCodeForm();
        $model = new DogCodeModel();
        $form->setInputFilter($model->getInputFilter());
        $form->init();
        return $form;
    }
}