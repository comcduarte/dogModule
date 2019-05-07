<?php 
namespace Dog\Form;

use User\Form\UserForm;

class OwnerForm extends UserForm
{
    public function __construct($name = null)
    {
        parent::__construct($name);
        
        $this->remove('PASSWORD');
        $this->remove('CONFIRM_PASSWORD');
    }
}
?>