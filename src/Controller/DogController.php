<?php 
namespace Dog\Controller;

use Annotation\Model\AnnotationModel;
use Dog\Form\BreedForm;
use Dog\Form\DogSearchForm;
use Dog\Model\DogModel;
use Dog\Model\LicenseModel;
use Midnet\Model\Uuid;
use User\Model\UserModel;
use Zend\Db\Adapter\AdapterAwareTrait;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Predicate\Like;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\View\Model\ViewModel;
use RuntimeException;

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
        $dogs = $dog->fetchAll($where, ['NAME']);
        
        
        $paginator = new Paginator(new ArrayAdapter($dogs));
        
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
            $post = array_merge_recursive(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
                );
            
            $this->form->setData($post);
            
            if ($this->form->isValid()) {
                $dog->exchangeArray($this->form->getData());
                
                $uuid = new Uuid();
                $dog->UUID = $uuid->value;
                
                $date = new \DateTime('now',new \DateTimeZone('EDT'));
                $today = $date->format('Y-m-d H:i:s');
                $dog->DATE_CREATED = $today;
                
                $dog->STATUS = $dog::ACTIVE_STATUS;
                
                $dog->create();
                
                return $this->redirect()->toRoute('dog/dog', ['action' => 'update', 'uuid' => $dog->UUID]);
            }
        }
        $breedForm = new BreedForm();
        $breedForm->init();
        
        return ([
            'form' => $this->form,
            'breedForm' => $breedForm,
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
            $post = array_merge_recursive(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
                );
            $this->form->setData($post);
            
            if ($this->form->isValid()) {
                $data = $this->form->getData();
                $model->PHOTO = $data->PHOTO['tmp_name'];
                $model->update();
                
                $this->flashmessenger()->addSuccessMessage('Update Successful');
                
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
        
        $breedForm = new BreedForm();
        $breedForm->init();
        
        return ([
            'annotations' => $notes,
            'breedForm' => $breedForm,
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
                $sql = new Sql($this->adapter);
                
                $select = new Select();
                $select->columns(['UUID','NAME','SEX','DESCRIPTION']);
                $select->from($dog->getTableName());
                
                
                
                $select->join('dog_users', 'dogs.UUID = dog_users.DOG', 'USER', Select::JOIN_LEFT)
                    ->join('users', 'dog_users.USER = users.UUID', 'ADDR1', Select::JOIN_LEFT)
                    ->join('dog_breeds', 'dogs.BREED = dog_breeds.UUID', 'BREED', Select::JOIN_LEFT);
                
                $predicate = new Where();
                $predicate->like('NAME', '%' . $data['NAME'] . '%');
                
                $select->where($predicate);
                $select->order('NAME');
                
                $statement = $sql->prepareStatementForSqlObject($select);
                $resultSet = new ResultSet();
                
                try {
                    $results = $statement->execute();
                    $resultSet->initialize($results);
                } catch (RuntimeException $e) {
                    return $e;
                }
                
                $dogs = $resultSet->toArray();
            }
        }
        
        
        
        $paginator = new Paginator(new ArrayAdapter($dogs));
        $paginator->setItemCountPerPage(0);
        
        $view = new ViewModel([
            'dogs' => $paginator,
            'count' => 0,
        ]);
        $view->setTemplate('dog/dog/find.phtml');
        
        return $view;
    }
}
