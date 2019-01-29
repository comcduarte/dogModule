<?php 
namespace Dog\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Db\Adapter\AdapterAwareTrait;
use Dog\Model\DogCodeModel;
use Zend\Db\Sql\Where;
use Midnet\Model\Uuid;

class DogCodeController extends AbstractActionController
{
    use AdapterAwareTrait;
    
    public $form;
    
    public function indexAction()
    {
        $code = new DogCodeModel($this->adapter);
        $where = new Where();
        $codes = $code->fetchAll($where, ['CODE']);
        
        return ([
            'codes' => $codes,
        ]);
    }
    
    public function createAction()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $code = new DogCodeModel($this->adapter);
            
            $this->form->setData($request->getPost());
            
            if ($this->form->isValid()) {
                $code->exchangeArray($this->form->getData());
                
                //-- Populate values not in form --//
                $uuid = new Uuid();
                $code->UUID = $uuid->value;
                
                $date = new \DateTime('now',new \DateTimeZone('EDT'));
                $today = $date->format('Y-m-d H:i:s');
                $code->DATE_CREATED = $today;
                $code->DATE_MODIFIED = $today;
                
                $code->STATUS = $code::ACTIVE_STATUS;
                
                $code->create();
                
                return $this->redirect()->toRoute('dog/code');
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
            return $this->redirect()->toRoute('dog/code');
        }
        
        $model = new DogCodeModel($this->adapter);
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
                return $this->redirect()->toRoute('dog/code');
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
            return $this->redirect()->toRoute('dog/code');
        }
        
        $model = new DogCodeModel($this->adapter);
        $model->read(['UUID' => $uuid]);
        $model->delete();
        
        return $this->redirect()->toRoute('dog/code');
    }
    
}