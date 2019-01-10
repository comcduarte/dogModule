<?php 
namespace Dog\Form\Factory;

use Interop\Container\ContainerInterface;
use Dog\Form\BreedForm;
use Dog\Model\BreedModel;

class BreedFormFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $form = new BreedForm();
        $model = new BreedModel();
        $form->setInputFilter($model->getInputFilter());
        $form->init();
        return $form;
    }
}