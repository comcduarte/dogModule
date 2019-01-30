<?php 
namespace Dog\Form\Factory;

use Interop\Container\ContainerInterface;
use Dog\Form\LicenseForm;
use Dog\Model\LicenseModel;

class LicenseFormFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $form = new LicenseForm();
        $model = new LicenseModel();
        
        $form->setInputFilter($model->getInputFilter());
        $form->initialize();
        
        return $form;
    }
}