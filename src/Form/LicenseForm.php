<?php 
namespace Dog\Form;

use Zend\Form\Form;
use Zend\Form\Element\Text;
use Zend\Form\Element\Csrf;
use Zend\Form\Element\Submit;
use Zend\Form\Element\Hidden;
use Zend\Form\Element\Select;
use Dog\Model\LicenseModel;

class LicenseForm extends Form
{
    public function initialize()
    {
        $this->add([
            'name' => 'TAG',
            'type' => Text::class,
            'attributes' => [
                'id' => 'TAG',
                'class' => 'form-control',
                'required' => 'true',
            ],
            'options' => [
                'label' => 'Tag Number',
            ],
        ]);
        
        $this->add([
            'name' => 'YEAR',
            'type' => Select::class,
            'attributes' => [
                'id' => 'TAG',
                'class' => 'form-control',
                'required' => 'true',
            ],
            'options' => [
                'label' => 'Year',
            ],
        ]);
        
        $this->add([
            'name' => 'STATUS',
            'type' => Select::class,
            'attributes' => [
                'id' => 'STATUS',
                'class' => 'form-control',
                'required' => 'true',
            ],
            'options' => [
                'label' => 'License Status',
                'value_options' => [
                    LicenseModel::INACTIVE_STATUS => 'Inactive',
                    LicenseModel::ACTIVE_STATUS => 'Active',
                ],
            ],
        ]);
        
        $this->add([
            'name' => 'PAYMENT_STATUS',
            'type' => Select::class,
            'attributes' => [
                'id' => 'PAYMENT_STATUS',
                'class' => 'form-control',
                'required' => 'true',
            ],
            'options' => [
                'label' => 'Payment Status',
                'value_options' => [
                    '0' => 'Unpaid',
                    '1' => 'Paid',
                ],
            ],
        ]);
        
        $this->add([
            'name' => 'FEE',
            'type' => Text::class,
            'attributes' => [
                'id' => 'FEE',
                'class' => 'form-control',
            ],
            'options' => [
                'label' => 'License Fee',
            ],
        ]);
        
        $this->add([
            'name' => 'DOG',
            'type' => Hidden::class,
            'attributes' => [
                'id' => 'DOG',
                'class' => 'form-control',
            ],
        ]);
        
        $this->add(new Csrf('SECURITY'));
        
        $this->add([
            'name' => 'SUBMIT',
            'type' => Submit::class,
            'attributes' => [
                'value' => 'Submit',
                'class' => 'btn btn-primary',
                'id' => 'SUBMIT',
            ],
        ]);
    }
}