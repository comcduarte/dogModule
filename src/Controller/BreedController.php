<?php 
namespace Dog\Controller;

use Dog\Model\BreedModel;
use Midnet\Model\Uuid;
use Zend\Db\Adapter\AdapterAwareTrait;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\DbSelect;

class BreedController extends AbstractActionController
{
    use AdapterAwareTrait;
    
    public $form;
    
    public function indexAction()
    {
        $breed = new BreedModel($this->adapter);
        $where = new Where();
        
        
        $select = new Select();
        $select->from($breed->getTableName());
        $select->where($where);
        $select->order(['BREED']);
        
        $paginator = new Paginator(new DbSelect($select, $this->adapter));
        $paginator->setDefaultScrollingStyle('All');
        
        $count = $this->params()->fromRoute('count', 15);
        
        $paginator->setCurrentPageNumber($this->params()->fromRoute('page', 1));
        $paginator->setItemCountPerPage($count);
        
        return ([
            'breeds' => $paginator,
        ]);
    }
    
    public function createAction()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $breed = new BreedModel($this->adapter);
            
            $this->form->setData($request->getPost());
            
            if ($this->form->isValid()) {
                $breed->exchangeArray($this->form->getData());
                
                //-- Populate values not in form --//
                $uuid = new Uuid();
                $breed->UUID = $uuid->value;
                
                $date = new \DateTime('now',new \DateTimeZone('EDT'));
                $today = $date->format('Y-m-d H:i:s');
                $breed->DATE_CREATED = $today;
                $breed->DATE_MODIFIED = $today;
                
                $breed->STATUS = $breed::ACTIVE_STATUS;
                
                $breed->create();
                
                return $this->redirect()->toRoute('dog/breed');
            }
        }
        
        return ([
            'form' => $this->form,
        ]);
    }
    
    public function updateAction()
    {
        $uuid = $this->params()->fromRoute('uuid',0);
        if (!$uuid) {
            return $this->redirect()->toRoute('dog/breed');
        }
        
        $model = new BreedModel($this->adapter);
        $model->read(['UUID' => $uuid]);
        
        $date = new \DateTime('now',new \DateTimeZone('EDT'));
        $today = $date->format('Y-m-d H:i:s');
        $model->DATE_MODIFIED = $today;
        
        $this->form->bind($model);
        $this->form->get('SUBMIT')->setAttribute('value','Update');
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $this->form->setData($request->getPost());
            
            if ($this->form->isValid()) {
                $model->update();
                return $this->redirect()->toRoute('dog/breed');
            }
        }
        
        return ([
            'form' => $this->form,
            'uuid' => $uuid,
        ]);
    }
    
    public function deleteAction()
    {
        $uuid = $this->params()->fromRoute('uuid', 0);
        if (!$uuid) {
            return $this->redirect()->toRoute('dog/breed');
        }
        
        $model = new BreedModel($this->adapter);
        $model->read(['UUID' => $uuid]);
        $model->delete();
        
        return $this->redirect()->toRoute('dog/breed');
    }
}
