<?php 
namespace Dog\Model;

use Midnet\Model\DatabaseObject;

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
}