<?php 
namespace Dog\Controller;

use Dog\Model\LicenseModel;
use Midnet\Model\Uuid;
use Zend\Db\Adapter\AdapterAwareTrait;
use Zend\Db\Sql\Where;
use Zend\Mvc\Controller\AbstractActionController;
use Dog\Model\DogModel;
use Dog\Form\DogUsersForm;
use Dog\Form\DogForm;
use Dog\Form\LicenseCodesForm;

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
        
        $DogModel = new DogModel($this->adapter);
        $dog = $DogModel->read(['UUID' => $model->DOG]);
        $owners = $dog->getOwners();
        
        $owners_form = new DogUsersForm('dog_owners_form');
        $owners_form->setDbAdapter($this->adapter);
        $owners_form->initialize();
        
        $dog_form = new DogForm('dog_form');
        $dog_form->setAttribute('action', $this->url()->fromRoute('dog/dog', ['action' => 'update', 'uuid' => $dog->UUID]));
        $dog_form->setDbAdapter($this->adapter);
        $dog_form->initialize();
        $dog_form->bind($dog);
        $dog_form->get('SUBMIT')->setAttribute('value','Update');
        
        $codes = $model->getCodes();
        $codes_form = new LicenseCodesForm('code_form');
        $codes_form->setDbAdapter($this->adapter);
        $codes_form->initialize();
        
        //-- Estimate License Fee --//
        $license_fee = 0;
        $gd = false;
        foreach ($codes as $code) {
            if ($code['CODE'] == "GD") {
                $gd = true;
            }
        }
//         $gd = array_search('GD', $codes);
        
        
        //-- Temporary test date --//
//         $date = new \DateTime('2019-02-25 00:00:00', new \DateTimeZone('EDT'));
        
        $year = $model->YEAR;
        $begin_registration = new \DateTime("$year-06-01 00:00:00",new \DateTimeZone('EDT'));
        $months = $date->diff($begin_registration);
//         $today = $date->format('Y-m-d H:i:s');
        switch (true) {
            case $gd:
                $license_fee = 0;
                break;
            case $date < $begin_registration:
                //-- Registering new dog before May --//
                $license_fee = 8;
                break;
            case $date > $begin_registration:
                $license_fee += 8;
            case $months->format('%m') > 1:
                $license_fee += ($months->format('%m'));
                break;
            default:
                $license_fee = 0;
                break;
        }
        
        return ([
            'form' => $this->form,
            'uuid' => $uuid,
            'owners' => $owners,
            'owners_form' => $owners_form,
            'dog' => $dog->getArrayCopy(),
            'dog_form' => $dog_form,
            'codes' => $codes,
            'codes_form' => $codes_form,
            'license_fee' => $license_fee,
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
    
    public function assigncodeAction()
    {
        $form = new LicenseCodesForm();
        $form->setDbAdapter($this->adapter);
        $form->initialize();
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());
            
            if ($form->isValid()) {
                $license = new LicenseModel($this->adapter);
                
                $data = $form->getData();
                $code_uuid = $data['CODE'];
                $license_uuid = $data['LICENSE'];
                
                $license->read(['UUID' => $license_uuid]);
                $license->assignCode($code_uuid);
            }
        }
        
        //-- Return to previous screen --//
        $url = $this->getRequest()->getHeader('Referer')->getUri();
        return $this->redirect()->toUrl($url);
    }
    
    public function unassigncodeAction()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            
            $model = new LicenseModel($this->adapter);
            $model->read(['UUID' => $data['LICENSE']]);
            $model->unassignCode($data['CODE']);
            
        }
        
        
        //-- Return to previous screen --//
        $url = $this->getRequest()->getHeader('Referer')->getUri();
        return $this->redirect()->toUrl($url);
    }
}