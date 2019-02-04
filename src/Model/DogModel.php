<?php 
namespace Dog\Model;

use Midnet\Model\DatabaseObject;
use Midnet\Model\Uuid;
use RuntimeException;
use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Delete;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Predicate\Like;
use User\Model\UserModel;

class DogModel extends DatabaseObject
{
    const MALE = 1;
    const FEMALE = 2;
    const NEUTERED = 3;
    const SPAYED = 4;
    
    public $UUID;
    public $NAME;
    public $BREED;
    public $SEX;
    public $DESCRIPTION;
    public $DATE_BIRTH;
    public $DATE_RABIESEXP;
    public $STATUS;
    public $DATE_CREATED;
    public $DATE_MODIFIED;
    
    public function __construct($dbAdapter = null)
    {
        parent::__construct($dbAdapter);
        
        $this->primary_key = 'UUID';
        $this->table = 'dogs';
    }
    
    public function assignUser($user) 
    {
        $sql = new Sql($this->dbAdapter);
        $uuid = new Uuid();
        
        $columns = [
            'UUID',
            'USER',
            'DOG',
        ];
        
        $values = [
            $uuid->value,
            $user,
            $this->UUID,
        ];
        
        $insert = new Insert();
        $insert->into('dog_users');
        $insert->columns($columns);
        $insert->values($values);
        
        $statement = $sql->prepareStatementForSqlObject($insert);
        
        try {
            $statement->execute();
        } catch (RuntimeException $e) {
            return $e;
        }
        return $this;
    }
    
    public function unassignUser($user)
    {
        $sql = new Sql($this->dbAdapter);
                
        $delete = new Delete();
        $delete->from('dog_users');
        $delete->where(['USER' => $user, 'DOG' => $this->UUID]);
        
        $statement = $sql->prepareStatementForSqlObject($delete);
        
        try {
            $statement->execute();
        } catch (RuntimeException $e) {
            return $e;
        }
        return $this;
    }
    
    public function getOwners()
    {
        $sql = new Sql($this->dbAdapter);
        
        $select = new Select();
        $select->columns(['USER']);
        $select->from('dog_users');
        $select->where([new Like('DOG', $this->UUID)]);
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $owners = $statement->execute();
        
        $owners_users = [];
        foreach ($owners as $owner) {
            $user = new UserModel($this->dbAdapter);
            $user->read(['UUID' => $owner['USER']]);
            $owners_users[] = $user->getArrayCopy();
        }
        
        return $owners_users;
    }
}