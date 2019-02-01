<?php 
namespace Dog\Form\Factory;

use Interop\Container\ContainerInterface;
use Dog\Form\BreedForm;
use Dog\Model\BreedModel;
use Midnet\Model\Uuid;

class BreedFormFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $uuid = new Uuid();
        
        $form = new BreedForm($uuid->value);
        $model = new BreedModel();
        $form->setInputFilter($model->getInputFilter());
        $form->init();
        return $form;
    }
}