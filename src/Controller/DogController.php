<?php 
namespace Dog\Controller;

use Annotation\Model\AnnotationModel;
use Dog\Form\DogSearchForm;
use Dog\Model\DogCodeModel;
use Dog\Model\DogModel;
use Dog\Model\LicenseModel;
use Midnet\Model\Uuid;
use User\Model\RoleModel;
use User\Model\UserModel;
use Zend\Crypt\Password\Bcrypt;
use Zend\Db\Adapter\AdapterAwareTrait;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Predicate\Like;
use Zend\Form\Form;
use Zend\Form\Element\Submit;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\DbSelect;
use RuntimeException;
use Zend\View\Model\ViewModel;
use Zend\Paginator\Adapter\ArrayAdapter;

class DogController extends AbstractActionController
{
    use AdapterAwareTrait;
    
    public $form;
    public $DogUsersForm;
    public $licenseForm;
    
    public function indexAction()
    {
        $dog = new DogModel($this->adapter);
        
        $where = new Where();
        
        $select = new Select();
        $select->from($dog->getTableName());
        $select->where($where);
        $select->order(['NAME']);
        
        $paginator = new Paginator(new DbSelect($select, $this->adapter));
        $paginator->setDefaultScrollingStyle('All');
        
        $count = $this->params()->fromRoute('count', 15);
        
        $paginator->setCurrentPageNumber($this->params()->fromRoute('page', 1));
        $paginator->setItemCountPerPage($count);
        
        return ([
            'dogs' => $paginator,
            'count' => $count,
        ]);
    }
    
    public function createAction()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $dog = new DogModel($this->adapter);
            
            $this->form->setData($request->getPost());
            
            if ($this->form->isValid()) {
                $dog->exchangeArray($this->form->getData());
                
                $uuid = new Uuid();
                $dog->UUID = $uuid->value;
                
                $date = new \DateTime('now',new \DateTimeZone('EDT'));
                $today = $date->format('Y-m-d H:i:s');
                $dog->DATE_CREATED = $today;
                
                $dog->STATUS = $dog::ACTIVE_STATUS;
                
                $dog->create();
                
                return $this->redirect()->toRoute('dog/dog');
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
            return $this->redirect()->toRoute('dog/dog');
        }
        
        $model = new DogModel($this->adapter);
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
                
                $url = $this->getRequest()->getHeader('Referer')->getUri();
                return $this->redirect()->toUrl($url);
            }
        }
        
        //-- BEGIN: Retrieve Annotations --//
        $annotation = new AnnotationModel($this->adapter);
        //$where = new Where(['TABLENAME' => 'dogs','PRIKEY' => $uuid]);
        $where = new Where([
            new Like('TABLENAME', 'dogs'),
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
        
        //-- BEGIN: Retrieve Owners --//
        $sql = new Sql($this->adapter);
        
        $select = new Select();
        $select->columns(['USER']);
        $select->from('dog_users');
        $select->where([new Like('DOG', $uuid)]);
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $owners = $statement->execute();
        
        $owners_users = [];
        foreach ($owners as $owner) {
            $user = new UserModel($this->adapter);
            $user->read(['UUID' => $owner['USER']]);
            $owners_users[] = $user->getArrayCopy();
        }
        
        //-- END: Retrieve Owners --//
        
        //-- BEGIN: Retrieve Licenses --//
        $where = new Where();
        $licenseModel = new LicenseModel($this->adapter);
        $licenses = $licenseModel->fetchAll($where->equalTo('DOG', $uuid), ['YEAR']);
        //-- END: Retrieve Licenses --//
        
        return ([
            'annotations' => $notes,
            'form' => $this->form,
            'uuid' => $uuid,
            'annotations_prikey' => $uuid,
            'annotations_tablename' => 'dogs',
            'annotations_user' => '',
            'owners_users' => $owners_users,
            'owners_form' => $this->DogUsersForm,
            'licenses' => $licenses,
            'licenses_form' => $this->licenseForm,
        ]);
    }
    
    public function deleteAction()
    {
        $uuid = $this->params()->fromRoute('uuid', 0);
        if (!$uuid) {
            return $this->redirect()->toRoute('dog/dog');
        }
        
        $model = new DogModel($this->adapter);
        $model->read(['UUID' => $uuid]);
        $model->delete();
        
        return $this->redirect()->toRoute('dog/dog');
    }
    
    public function assignuserAction()
    {
        $form = $this->DogUsersForm;
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());
            
            if ($form->isValid()) {
                $dog = new DogModel($this->adapter);
                $data = $form->getData();
                $dog_uuid = $data['DOG'];
                $user_uuid = $data['USER'];
                $dog->read(['UUID' => $dog_uuid]);
                $dog->assignUser($user_uuid);
                $dog->update();
            }
        }
        
        //-- Return to previous screen --//
        $url = $this->getRequest()->getHeader('Referer')->getUri();
        return $this->redirect()->toUrl($url);
    }
    
    public function unassignuserAction()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            
            $model = new DogModel($this->adapter);
            $model->read(['UUID' => $data['DOG']]);
            $model->unassignUser($data['USER']);
            
        }
        
        
        
        $url = $this->getRequest()->getHeader('Referer')->getUri();
        return $this->redirect()->toUrl($url);
    }
    
    public function findAction() 
    {
        $dog = new DogModel($this->adapter);
        $form = new DogSearchForm();
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            
            $data = $request->getPost();
            $form->setData($data);
            
            if ($form->isValid()) {
                $predicate = new Where();
                $predicate->like('NAME', '%' . $data['NAME'] . '%');
                $dogs = $dog->fetchAll($predicate, ['NAME']);
            }
        }
        
        $paginator = new Paginator(new ArrayAdapter($dogs));
        
        $view = new ViewModel([
            'dogs' => $paginator,
            'count' => 0,
        ]);
        $view->setTemplate('dog/dog/index.phtml');
        
        return $view;
    }
    
    public function importAction()
    {
        $form = new Form();
        $form->add([
            'name' => 'SUBMIT',
            'type' => Submit::class,
            'attributes' => [
                'value' => 'Submit',
                'class' => 'btn btn-primary',
                'id' => 'SUBMIT',
            ],
        ]);
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());
            
            if ($form->isValid()) {
                
                $sql = new Sql($this->adapter);
                
                $select = new Select();
                $select->from('import');
//                 $select->limit(10);
                
                $statement = $sql->prepareStatementForSqlObject($select);
                
                try {
                    $resultSet = $statement->execute();
                } catch (RuntimeException $e) {
                    return $e;
                }
                
                foreach ($resultSet as $record) {
                    $uuid = new Uuid();
                    $date = new \DateTime('now',new \DateTimeZone('EDT'));
                    $today = $date->format('Y-m-d H:i:s');
                    $year = $date->format('Y');
                    
                    
                    
                    $owner = new UserModel($this->adapter);
                    $result = $owner->read([
                        'LNAME' => $record['LNAME'],
                        'FNAME' => $record['FNAME'],
                        'ADDR1' => $record['ADDRESS'],
                    ]);
                    
                    if (is_null($result->UUID)) {
                        $bcrypt = new Bcrypt();
                        //-- User does not exist --//
                        
                        //-- Create unavailable data for required fields --//
                        $owner->UUID = $uuid->value;
                        $owner->USERNAME = substr($uuid->value, 0, 8);
                        $owner->PASSWORD = $bcrypt->create(substr($uuid->value, 24, 12));
                        $owner->DATE_CREATED = $today;
                        $owner->DATE_MODIFIED = $today;
                        
                        $owner->FNAME = $record['FNAME'];
                        $owner->LNAME = $record['LNAME'];
                        $owner->ADDR1 = $record['ADDRESS'];
                        $owner->CITY = "Middletown";
                        $owner->STATE = "CT";
                        $owner->ZIP = "06457";
                        
                        //-- Ensure properly formatted phone --//
                        $pattern = '/\((\d+)\) (\d+)-(\d+)/';
                        $replacement = '$1$2$3';
                        $owner->PHONE = preg_replace($pattern, $replacement, $record['PHONE']);
                        
                        $owner->create();
                        
                        $role = new RoleModel($this->adapter);
                        $role->read(['ROLENAME' => 'Owners']);
                        $uuid->generate();
                        
                        
                        $owner->assignRole([
                            'UUID' => $uuid->value,
                            'USER' => $owner->UUID,
                            'ROLE' => $role->UUID,
                        ]);
                    }
                    
                    $uuid = new Uuid();
                    $dog = new DogModel($this->adapter);
                    
                    $rabies = new \DateTime($record['RABIES EXP'],new \DateTimeZone('EDT'));
                    $rabies_formatted = $rabies->format('Y-m-d');
                    
                    $result = $dog->read([
                        'NAME' => $record['DOG NAME'],
                        'DATE_RABIESEXP' => $rabies_formatted,
                    ]);
                    
                    if (is_null($result->UUID)) {
                        $dog->UUID = $uuid->value;
                        
                        $dog->NAME = $record['DOG NAME'];
                        $dog->DESCRIPTION = $record['COLOR'] . "\r\n" . $record['BREED'];
                        $dog->DATE_RABIESEXP = $rabies_formatted;
                        $dog->DATE_CREATED = $today;
                        $dog->DATE_MODIFIED = $today;
                        $dog->DATE_BIRTH = $year - $record['AGE'] . "-01-01 00:00:00";
                        
                        //-- Determine Dog Sex --//
                        switch ($record['SEX']) {
                            case 'M':
                                $dog->SEX = $dog::MALE;
                                break;
                            case 'F':
                                $dog->SEX = $dog::FEMALE;
                                break;
                            case 'N':
                                $dog->SEX = $dog::NEUTERED;
                                break;
                            case 'S':
                                $dog->SEX = $dog::SPAYED;
                                break;
                        }
                        
                        
                        $dog->create();
                        
                        $dog->assignUser($owner->UUID);
                    }
                    
                    
                    
                    
                    $code = new DogCodeModel($this->adapter);
                    $code->read(['CODE' => $record['COD']]);
                    
                    //-- Add code to secondary Table --//
                    $license = new LicenseModel($this->adapter);
                    
                    
                    if ($record['TAG#'] != 0) {
                        $result = $license->read(['TAG' => $record['TAG#']]);
                        
                        if (is_null($result->UUID)) {
                            $uuid = new Uuid();
                            
                            
                            $license->UUID = $uuid->value;
                            $license->DATE_CREATED = $today;
                            $license->DATE_MODIFIED = $today;
                            $license->TAG = $record['TAG#'];
                            $license->YEAR = "2018";
                            $license->STATUS = $license::ACTIVE_STATUS;
                            $license->PAYMENT_STATUS = $license::ACTIVE_STATUS;
                            $license->DOG = $dog->UUID;
                            $license->FEE = $record['FEE'];
                            
                            if ($code->UUID) {
                                $license->assignCode($code->UUID);
                            }
                            
                            $license->create();
                        }
                    }
                }
            }
        }
        
        
            
        
        return ([
            'form' => $form,
        ]);
    }
}
