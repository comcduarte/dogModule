<?php 
namespace Dog\Form;

use Zend\Form\Form;
use Zend\Form\Element\Csrf;
use Zend\Form\Element\Submit;
use Zend\Form\Element\Text;
use Zend\Form\Element\Textarea;

class ReportForm extends Form
{
    public function initialize()
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
                'label' => 'Report Name',
            ],
        ]);
        
        $this->add([
            'name' => 'CODE',
            'type' => Textarea::class,
            'attributes' => [
                'id' => 'CODE',
                'class' => 'form-control',
                'required' => 'true',
            ],
            'options' => [
                'label' => 'SQL Statement',
            ],
        ]);
        
        $this->add([
            'name' => 'VIEW',
            'type' => Text::class,
            'attributes' => [
                'id' => 'VIEW',
                'class' => 'form-control',
                'required' => 'true',
            ],
            'options' => [
                'label' => 'PHTML File Location',
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