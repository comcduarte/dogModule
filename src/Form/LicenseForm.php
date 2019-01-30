<?php 
namespace Dog\Form;

use Zend\Form\Form;
use Zend\Form\Element\Text;
use Zend\Form\Element\Csrf;
use Zend\Form\Element\Submit;
use Zend\Form\Element\Hidden;

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