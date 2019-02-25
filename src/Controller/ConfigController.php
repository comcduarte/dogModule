<?php 
namespace Dog\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Db\Adapter\AdapterAwareTrait;
use Zend\View\Model\ViewModel;

class ConfigController extends AbstractActionController
{
    use AdapterAwareTrait;
    
    public function indexAction()
    {
        $view = new ViewModel();
        
        return $view;
    }
}