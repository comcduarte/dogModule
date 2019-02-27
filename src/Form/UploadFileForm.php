<?php 
namespace Dog\Form;

use Zend\Form\Form;
use Zend\Form\Element\Csrf;
use Zend\Form\Element\File;
use Zend\Form\Element\Submit;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\FileInput;

class UploadFileForm extends Form
{
    public function initialize()
    {
        $this->add([
            'name' => 'FILE',
            'type' => File::class,
            'attributes' => [
                'id' => 'FILE',
                'class' => 'form-control',
            ],
            'options' => [
                'label' => 'Upload File',
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
    
    public function addInputFilter()
    {
        $inputFilter = new InputFilter();
        
        $fileInput = new FileInput('FILE');
        $fileInput->getFilterChain()->attachByName(
            'filerenameupload',
            array(
                'target'    => './data/',
                'randomize' => true,
            )
            );
        $inputFilter->add($fileInput);
        
        $this->setInputFilter($inputFilter);
    }
}