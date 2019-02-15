<?php 
namespace Dog\Model;

use Midnet\Model\DatabaseObject;

class ReportModel extends DatabaseObject
{
    public $UUID;
    public $NAME;
    public $CODE;
    public $VIEW;
    
    public function __construct($dbAdapter = null)
    {
        parent::__construct($dbAdapter);
        
        $this->primary_key = 'UUID';
        $this->table = 'reports';
    }
}