<?php 
namespace Dog\Form;

use Zend\Form\Form;
use Zend\Form\Element\Csrf;
use Zend\Form\Element\Submit;
use Zend\Form\Element\Text;

class DogForm extends Form
{
    public function init()
    {
        $this->add([
            'name' => 'NAME',
            'type' => Text::class,
            'attributes' => [
                'id' => 'NAME',
                'class' => 'form-control',
                'required' => 'true',
            ],
            'options' => [
                'label' => 'Dog Name',
            ],
        ]);
        
        $this->add([
            'name' => 'BREED',
            'type' => Text::class,
            'attributes' => [
                'id' => 'BREED',
                'class' => 'form-control',
                'required' => 'true',
            ],
            'options' => [
                'label' => 'Breed',
            ],
        ]);
        
        $this->add([
            'name' => 'SEX',
            'type' => Text::class,
            'attributes' => [
                'id' => 'SEX',
                'class' => 'form-control',
            ],
            'options' => [
                'label' => 'Sex',
            ],
        ]);
        
        $this->add([
            'name' => 'DATE_BIRTH',
            'type' => Text::class,
            'attributes' => [
                'id' => 'DATE_BIRTH',
                'class' => 'form-control',
            ],
            'options' => [
                'label' => 'Birthdate',
            ],
        ]);
        
        $this->add([
            'name' => 'DATE_RABIESEXP',
            'type' => Text::class,
            'attributes' => [
                'id' => 'DATE_RABIESEXP',
                'class' => 'form-control',
            ],
            'options' => [
                'label' => 'Rabies Expiration Date',
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