<?php 
namespace Dog\Model;

use User\Model\UserModel;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\InputFilter\InputFilter;
use RuntimeException;

class OwnerModel extends UserModel
{
    public function getDogs()
    {
        //-- Retrieve list of dogs belonging to this owner. --//
        $sql = new Sql($this->dbAdapter);
        
        $select = new Select();
        $select->from('dog_users')->where(['USER' => $this->UUID]);
        
        $statement = $sql->prepareStatementForSqlObject($select);
        
        try {
            $resultSet = $statement->execute();
        } catch (RuntimeException $e) {
            return $e;
        }
        
        $dogs = [];
        foreach ($resultSet as $record) {
            $dog = new DogModel($this->dbAdapter);
            $dog->read(['UUID' => $record['DOG']]);
            $dogs[] = $dog->getArrayCopy();
        }
        
        return $dogs;
    }
    
    public function getInputFilter()
    {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();
            
            /**
             * Removed PASSWORD and CONFIRM_PASSWORD filters, and passed blank input filter
             */
            
            $this->inputFilter = $inputFilter;
        }
        return $this->inputFilter;
    }
}