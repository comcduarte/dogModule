<?php 
namespace Dog\Model;

use Midnet\Model\DatabaseObject;
use Midnet\Model\Uuid;
use User\Model\UserModel;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Delete;
use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Predicate\Like;
use Zend\Db\Sql\Predicate\Predicate;
use RuntimeException;

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
    
    public function fetchAll(Predicate $predicate = NULL, array $order = [])
    {
        if ($predicate == null) {
            $predicate = new Where();
        }
        
        $sql = new Sql($this->dbAdapter);
        
        $select = new Select();
        $select->from($this->table);
        $select->join('dog_breeds', 'dogs.BREED = dog_breeds.UUID', ['BREED'], Select::JOIN_INNER);
        $select->where($predicate);
        $select->order($order);
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = new ResultSet();
        try {
            $results = $statement->execute();
            $resultSet->initialize($results);
        } catch (RuntimeException $e) {
            return $e;
        }
        
        return $resultSet->toArray();
        
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
    
    public static function getGenderDescription($gender) 
    {
        switch ($gender) {
            case DogModel::MALE:
                return "Male";
                break;
            case DogModel::FEMALE:
                return "Female";
                break;
            case DogModel::NEUTERED:
                return "Neutered";
                break;
            case DogModel::SPAYED:
                return "Spayed";
                break;
        }
    }
}