<?php 
namespace Dog\Model;

use Midnet\Model\DatabaseObject;

class BreedModel extends DatabaseObject
{
    public $UUID;
    public $BREED;
    public $STATUS;
    public $DATE_CREATED;
    public $DATE_MODIFIED;
    
    public function __construct($dbAdapter = null)
    {
        parent::__construct($dbAdapter);
        
        $this->primary_key = 'UUID';
        $this->table = 'dog_breeds';
    }
}