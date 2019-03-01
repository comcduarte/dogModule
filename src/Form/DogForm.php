<?php 
namespace Dog\Form;

use Dog\Model\DogModel;
use Midnet\Form\Element\DatabaseSelectObject;
use Zend\Form\Form;
use Zend\Form\Element\Csrf;
use Zend\Form\Element\Date;
use Zend\Form\Element\Radio;
use Zend\Form\Element\Submit;
use Zend\Form\Element\Text;
use Zend\Db\Adapter\AdapterAwareTrait;
use Zend\Form\Element\Textarea;

class DogForm extends Form
{
    use AdapterAwareTrait;
    
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
                'label' => 'Dog Name',
            ],
        ]);
        
        $breed = new DatabaseSelectObject('BREED');
        $breed->setName('BREED');
        $breed->setDbAdapter($this->adapter);
        $breed->setAttributes([
            'id' => 'BREED',
            'class' => 'custom-select',
            'required' => 'true',
        ]);
        $breed->setOptions([
            'database_table' => 'dog_breeds',
            'database_id_column' => 'UUID',
            'database_value_column' => 'BREED',
            'label' => 'Breed',
        ]);
        $breed->populateElement();
        $this->add($breed);
        
        $this->add([
            'name' => 'SEX',
            'type' => Radio::class,
            'attributes' => [
                'id' => 'SEX',
                'class' => 'form-control',
            ],
            'options' => [
                'label' => 'Sex',
                'value_options' => [
                    [
                        'value' => DogModel::MALE,
                        'label' => 'Male',
                        'disabled' => false,
                        'attributes' => [
                            'class' => 'form-check',
                        ],
                    ],
                    [
                        'value' => DogModel::FEMALE,
                        'label' => 'Female',
                        'disabled' => false,
                        'attributes' => [
                            'class' => 'form-check',
                        ],
                    ],
                    [
                        'value' => DogModel::NEUTERED,
                        'label' => 'Neutered',
                        'disabled' => false,
                        'attributes' => [
                            'class' => 'form-check',
                        ],
                    ],
                    [
                        'value' => DogModel::SPAYED,
                        'label' => 'Spayed',
                        'disabled' => false,
                        'attributes' => [
                            'class' => 'form-check',
                        ],
                    ],
                ],
            ],
        ]);
        
        $this->add([
            'name' => 'DESCRIPTION',
            'type' => Textarea::class,
            'attributes' => [
                'id' => 'DESCRIPTION',
                'class' => 'form-control',
            ],
            'options' => [
                'label' => 'Description',
            ],
        ]);
        
        $this->add([
            'name' => 'DATE_BIRTH',
            'type' => Date::class,
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
            'type' => Date::class,
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