<?php 
namespace Dog\Form;

use Zend\Form\Form;
use Zend\Db\Adapter\AdapterAwareTrait;
use Midnet\Model\Uuid;
use Zend\Form\Element\Hidden;
use Zend\Form\Element\Select;
use Zend\Form\Element\Csrf;
use Zend\Form\Element\Submit;
use RuntimeException;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Select as SqlSelect;

class LicenseCodesForm extends Form
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
            'name' => 'LICENSE',
            'type' => Hidden::class,
            'attributes' => [
                'value' => '',
                'id' => 'LICENSE',
            ],
        ]);
        
        $this->add([
            'name' => 'CODE',
            'type' => Select::class,
            'attributes' => [
                'class' => 'form-control',
                'id' => 'CODE',
                
            ],
            'options' => [
                'label' => 'Select Code',
                'value_options' => $this->getSelectValueOptions('view_licensecodesform', 'uuid', 'code'),
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