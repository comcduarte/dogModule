<?php 
namespace Dog\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Db\Adapter\AdapterAwareTrait;

class DogController extends AbstractActionController
{
    use AdapterAwareTrait;
    
    public function indexAction()
    {
        return ([]);
    }
}