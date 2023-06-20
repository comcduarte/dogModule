<?php 
namespace Dog\Form;

use Zend\Form\Form;
use Zend\Form\Element\Csrf;
use Zend\Form\Element\Hidden;
use Zend\Form\Element\Select;
use Zend\Form\Element\Submit;
use Zend\Form\Element\Text;

class LicenseSearchForm extends Form
{
    public function initialize()
    {
        $this->add([
            'name' => 'FIELD',
            'type' => Hidden::class,
            'attributes' => [
                'value' => 'TAG',
            ],
        ]);
        
        $this->add([
            'name' => 'TAG',
            'type' => Text::class,
            'attributes' => [
                'class' => 'form-control',
                'id' => 'TAG',
                'required' => 'true',
                'placeholder' => '',
            ],
            'options' => [
                'label' => 'Tag',
            ],
        ]);
        
        $this->add([
            'name' => 'YEAR',
            'type' => Select::class,
            'attributes' => [
                'class' => 'form-control',
                'id' => 'YEAR',
                'required' => 'true',
                'placeholder' => '',
            ],
            'options' => [
                'label' => 'Year',
                'value_options' => [
                    "2023-2024" => "2023-2024",
                    "2022-2023" => "2022-2023",
                    "2021-2022" => "2021-2022",
                    "2020-2021" => "2020-2021",
                    "2019-2020" => "2019-2020",
                    "2018-2019" => "2018-2019",
                    "2017-2018" => "2017-2018",
                ],
            ],
        ]);
        
        $this->add(new Csrf('SECURITY'));
        
        $this->add([
            'name' => 'SUBMIT',
            'type' => Submit::class,
            'attributes' => [
                'value' => 'Search',
                'class' => 'btn btn-primary',
                'id' => 'SUBMIT',
            ],
        ]);
    }
}