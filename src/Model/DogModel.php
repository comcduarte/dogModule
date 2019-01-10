<?php 
namespace Dog\Model;

use Midnet\Model\DatabaseObject;

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
}