<?php 
namespace Dog\Model;

use Midnet\Model\DatabaseObject;

class DogCodeModel extends DatabaseObject
{
    public $UUID;
    public $CODE;
    public $DESCRIPTION;
    public $STATUS;
    public $DATE_CREATED;
    public $DATE_MODIFIED;
    
    public function __construct($dbAdapter = null)
    {
        parent::__construct($dbAdapter);
        
        $this->primary_key = 'UUID';
        $this->table = 'dog_codes';
    }
}