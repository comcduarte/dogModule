<?php 
namespace Dog\Controller;

use Dog\Model\LicenseModel;
use Midnet\Model\Uuid;
use Zend\Db\Adapter\AdapterAwareTrait;
use Zend\Db\Sql\Where;
use Zend\Mvc\Controller\AbstractActionController;

class LicenseController extends AbstractActionController
{
    use AdapterAwareTrait;
    
    public $form;
    public $model;
    
    public function indexAction()
    {
        $model = new LicenseModel($this->adapter);
        $licenses = $model->fetchAll(new Where(), ['TAG']);
        
        return ([
            'licenses' => $licenses,
        ]);
    }
    
    public function createAction()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $model = new LicenseModel($this->adapter);
            
            $this->form->setData($request->getPost());
            
            if ($this->form->isValid()) {
                $model->exchangeArray($this->form->getData());
                
                $uuid = new Uuid();
                $model->UUID = $uuid->value;
                
                $date = new \DateTime('now',new \DateTimeZone('EDT'));
                $today = $date->format('Y-m-d H:i:s');
                $model->DATE_CREATED = $today;
                $model->DATE_MODIFIED = $today;
                
                $model->STATUS = $model::ACTIVE_STATUS;
                
                $model->create();
                
                //-- Return to previous screen --//
                $url = $this->getRequest()->getHeader('Referer')->getUri();
                return $this->redirect()->toUrl($url);
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
            //-- Return to previous screen --//
            $url = $this->getRequest()->getHeader('Referer')->getUri();
            return $this->redirect()->toUrl($url);
        }
        
        $model = new LicenseModel($this->adapter);
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
                //-- Return to previous screen --//
                $url = $this->getRequest()->getHeader('Referer')->getUri();
                return $this->redirect()->toUrl($url);
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
            return $this->redirect()->toRoute('dog/license');
        }
        
        $model = new LicenseModel($this->adapter);
        $model->read(['UUID' => $uuid]);
        $model->delete();
        
        //-- Return to previous screen --//
        $url = $this->getRequest()->getHeader('Referer')->getUri();
        return $this->redirect()->toUrl($url);
    }
}