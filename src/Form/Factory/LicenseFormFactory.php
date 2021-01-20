<?php 
namespace Dog\Form\Factory;

use Interop\Container\ContainerInterface;
use Dog\Form\LicenseForm;
use Dog\Model\LicenseModel;
use Midnet\Model\Uuid;

class LicenseFormFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $uuid = new Uuid();
        
        $form = new LicenseForm($uuid->value);
        $model = new LicenseModel();
        
        $form->setInputFilter($model->getInputFilter());
        $form->initialize();
        
        $date = new \DateTime('now',new \DateTimeZone('EDT'));
        $year = $date->format('Y');
        
        $value_options = [];
        
        if (date('m') <= 6) {
            //-- Prior to July --//
            $value = sprintf("%s-%s",$year - 1, $year);
            $value_options[$value] = $value;
        }
        
        for ($i = 0; $i < 2; $i++) {
            $value = sprintf("%s-%s",$year + $i, $year + $i + 1);
            $value_options[$value] = $value;
        }
        $form->get('YEAR')->setOptions(['value_options' => $value_options]);
        
        
        return $form;
    }
}