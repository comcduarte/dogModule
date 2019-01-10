<?php 
namespace Dog\Form;

use Zend\Form\Form;
use Zend\Form\Element\Csrf;
use Zend\Form\Element\Submit;
use Zend\Form\Element\Text;

class BreedForm extends Form
{
    public function init()
    {
        $this->add([
            'name' => 'BREED',
            'type' => Text::class,
            'attributes' => [
                'id' => 'BREED',
                'class' => 'form-control',
                'required' => 'true',
            ],
            'options' => [
                'label' => 'Breed Name',
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