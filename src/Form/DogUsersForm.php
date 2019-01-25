<?php 
namespace Dog\Form;

use Midnet\Model\Uuid;
use Zend\Db\Adapter\AdapterAwareTrait;
use Zend\Db\Sql\Select as SqlSelect;
use Zend\Db\Sql\Sql;
use Zend\Form\Form;
use Zend\Form\Element\Csrf;
use Zend\Form\Element\Hidden;
use Zend\Form\Element\Submit;
use Zend\Form\Element\Select;
use RuntimeException;

class DogUsersForm extends Form
{
    use AdapterAwareTrait;
    
    public function __construct($name = NULL, $options = [])
    {
        parent::__construct($name);
    }
    
    public function initialize()
    {
        $uuid = new Uuid();
        
        
        $this->add([
            'name' => 'UUID',
            'type' => Hidden::class,
            'attributes' => [
                'value' => $uuid->value,
                'id' => 'UUID',
            ],
        ]);
        
        $this->add([
            'name' => 'DOG',
            'type' => Hidden::class,
            'attributes' => [
                'class' => 'form-control',
                'id' => 'DOG',
                'value' => '',
            ],
        ]);
        
        $this->add([
            'name' => 'USER',
            'type' => Select::class,
            'attributes' => [
                'class' => 'form-control',
                'id' => 'USER',
                
            ],
            'options' => [
                'label' => 'Select Owner',
                'value_options' => $this->getSelectValueOptions('view_dogusersform', 'uuid', 'name'),
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
    
    public function getSelectValueOptions($table = null, $id_col = null, $val_col = null)
    {
        $options = [];
        
        $sql = new Sql($this->adapter);
        
        $select = new SqlSelect();
        $select->from($table);
        $select->columns([$id_col => $id_col, $val_col => $val_col]);
        
        $statement = $sql->prepareStatementForSqlObject($select);
        
        try {
            $resultSet = $statement->execute();
        } catch (RuntimeException $e) {
            return $e;
        }
        
        foreach ($resultSet as $object) {
            $options[$object[$id_col]] = $object[$val_col];
        }
        
        return $options;
    }
}