<?php 
namespace Dog\Model;

use Midnet\Model\DatabaseObject;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Predicate\Like;
use Midnet\Model\Uuid;
use Zend\Db\Sql\Insert;
use RuntimeException;
use Zend\Db\Sql\Delete;

class LicenseModel extends DatabaseObject
{
    public $UUID;
    public $TAG;
    public $DOG;
    public $PAYMENT_STATUS;
    public $STATUS;
    public $FEE;
    public $YEAR;
    public $DATE_CREATED;
    public $DATE_MODIFIED;
    
    public function __construct($dbAdapter = null)
    {
        parent::__construct($dbAdapter);
        
        $this->primary_key = 'UUID';
        $this->table = 'dog_licenses';
    }
    
    public function getCodes()
    {
        $sql = new Sql($this->dbAdapter);
        
        $select = new Select();
        $select->columns(['UUID','CODE']);
        $select->from('license_codes');
        $select->where([new Like('LICENSE', $this->UUID)]);
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $codes = $statement->execute();
        
        $license_codes = [];
        foreach ($codes as $uuid) {
            $code = new DogCodeModel($this->dbAdapter);
            $code->read(['UUID' => $uuid['CODE']]);
            $license_codes[$uuid['UUID']] = $code->getArrayCopy();
        }
        
        return $license_codes;
    }
    
    public function assignCode($code_uuid)
    {
        $sql = new Sql($this->dbAdapter);
        $uuid = new Uuid();
        
        $columns = [
            'UUID',
            'CODE',
            'LICENSE',
        ];
        
        $values = [
            $uuid->value,
            $code_uuid,
            $this->UUID,
        ];
        
        $insert = new Insert();
        $insert->into('license_codes');
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
    
    public function unassignCode($code_uuid)
    {
        $sql = new Sql($this->dbAdapter);
        
        $delete = new Delete();
        $delete->from('license_codes');
        $delete->where(['UUID' => $code_uuid]);
        
        $statement = $sql->prepareStatementForSqlObject($delete);
        
        try {
            $statement->execute();
        } catch (RuntimeException $e) {
            return $e;
        }
        return $this;
    }
}