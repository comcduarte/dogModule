<?php 
namespace Dog\Form;

use Zend\Form\Form;
use Zend\Form\Element\Text;
use Zend\Form\Element\Csrf;
use Zend\Form\Element\Submit;

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