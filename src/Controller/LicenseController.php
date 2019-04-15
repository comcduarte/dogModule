<?php 
namespace Dog\Controller;

use Annotation\Model\AnnotationModel;
use Dog\Form\DogForm;
use Dog\Form\DogUsersForm;
use Dog\Form\LicenseCodesForm;
use Dog\Form\LicenseSearchForm;
use Dog\Model\DogModel;
use Dog\Model\LicenseModel;
use Midnet\Model\Uuid;
use User\Model\UserModel;
use Zend\Db\Adapter\AdapterAwareTrait;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Predicate\Like;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Paginator\Adapter\DbSelect;
use Zend\View\Model\ViewModel;
use Dog\Form\BreedForm;

class LicenseController extends AbstractActionController
{
    use AdapterAwareTrait;
    
    public $form;
    public $model;
    
    public function indexAction()
    {
        $model = new LicenseModel($this->adapter);
        $where = new Where();
        
        $select = new Select();
        $select->from($model->getTableName());
        $select->where($where);
        $select->order(['TAG']);
        
        $paginator = new Paginator(new DbSelect($select, $this->adapter));
        $paginator->setDefaultScrollingStyle('All');
        
        $count = $this->params()->fromRoute('count', 15);
        
        $paginator->setCurrentPageNumber($this->params()->fromRoute('page', 1));
        $paginator->setItemCountPerPage($count);
        
        return ([
            'licenses' => $paginator,
            'pageCount' => $count,
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
                $this->flashmessenger()->addSuccessMessage('Update Successful');
                
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
        
        $breedForm = new BreedForm();
        $breedForm->init();
        
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
        
        $date = new \DateTime('now',new \DateTimeZone('EDT'));
        $year = $date->format('Y');
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
        
        //-- BEGIN: Retrieve Annotations --//
        $annotation = new AnnotationModel($this->adapter);
        //$where = new Where(['TABLENAME' => 'dogs','PRIKEY' => $uuid]);
        $where = new Where([
            new Like('TABLENAME', 'dog_licenses'),
            new Like('PRIKEY', $uuid),
        ]);
        $annotations = $annotation->fetchAll($where, ['DATE_CREATED DESC']);
        
        $notes = [];
        foreach ($annotations as $annotation) {
            $user = new UserModel($this->adapter);
            $user->read(['UUID' => $annotation['USER']]);
            
            $notes[] = [
                'USER' => $user->USERNAME,
                'ANNOTATION' => $annotation['ANNOTATION'],
                'DATE_CREATED' => $annotation['DATE_CREATED'],
            ];
        }
        //-- END: Retrieve Annotations --//
        
        return ([
            'annotations' => $notes,
            'breedForm' => $breedForm,
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
    
    public function licenseAction()
    {
        $this->layout('layout/license');
        $view = new ViewModel();
        
        $uuid = $this->params()->fromRoute('uuid', 0);
        if (!$uuid) {
            return $this->redirect()->toRoute('dog/license');
        }
        
        $license = new LicenseModel($this->adapter);
        $license->read(['UUID' => $uuid]);
        $codes = $license->getCodes();
        
        $dog = new DogModel($this->adapter);
        $dog->read(['UUID' => $license->DOG]);
        
        $owners = $dog->getOwners();
        
        $view->setVariables([
            'license' => $license,
            'codes' => $codes,
            'dog' => $dog,
            'owners' => $owners,
        ]);
        
        return $view;
    }
    
    public function findAction()
    {
        $license = new LicenseModel($this->adapter);
        $form = new LicenseSearchForm();
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            
            $data = $request->getPost();
            $form->setData($data);
            
            if ($form->isValid()) {
                $where = new Where();
                $where->like('TAG', $data['TAG']);
                $where->like('YEAR', $data['YEAR']);
                $licenses = $license->fetchAll($where, ['TAG']);
            }
            

        }
        
        $paginator = new Paginator(new ArrayAdapter($licenses));
        $paginator->setItemCountPerPage(0);
        
        $view = new ViewModel([
            'licenses' => $paginator,
        ]);
        $view->setTemplate('dog/license/index.phtml');
        
        return $view;
    }
}