<?php 
namespace Dog\Controller;

use Annotation\Model\AnnotationModel;
use Dog\Form\OwnerForm;
use Dog\Form\OwnerSearchForm;
use Dog\Model\OwnerModel;
use Midnet\Model\Uuid;
use User\Form\UserForm;
use User\Model\RoleModel;
use User\Model\UserModel;
use Zend\Db\Adapter\AdapterAwareTrait;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Predicate\Like;
use Zend\Form\Element\Hidden;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\DbSelect;

class OwnerController extends AbstractActionController
{
    use AdapterAwareTrait;
    
    public function indexAction()
    {
        $owner = new OwnerModel($this->adapter);
        $where = new Where();
        
        $select = new Select();
        $select->from($owner->getTableName());
        $select->where($where);
        $select->order(['LNAME']);
        
        $paginator = new Paginator(new DbSelect($select, $this->adapter));
        $paginator->setDefaultScrollingStyle('All');
        
        $count = $this->params()->fromRoute('count', 15);
        
        $paginator->setCurrentPageNumber($this->params()->fromRoute('page', 1));
        $paginator->setItemCountPerPage($count);
        
        return ([
            'owners' => $paginator,
        ]);
    }
    
    public function createAction()
    {
        $request = $this->getRequest();
        $form = new UserForm();
        
        $uuid = new Uuid();
        
        $form->remove('USERNAME');
        $username = new Hidden('USERNAME');
        $username->setValue($uuid->generate()->value);
        $form->add($username);
        
        $form->remove('PASSWORD');
        $password = new Hidden('PASSWORD');
        $password->setValue($uuid->generate()->value);
        $form->add($password);
        
        $form->remove('CONFIRM_PASSWORD');
        $cpassword = new Hidden('CONFIRM_PASSWORD');
        $cpassword->setValue($uuid->value);
        $form->add($cpassword);
        
        $form->get('CITY')->setAttribute('value', 'Middletown');
        $form->get('STATE')->setAttribute('value', 'CT');  
        $form->get('ZIP')->setAttribute('value', '06457');       
        
        if ($request->isPost()) {
            $owner = new OwnerModel($this->adapter);
            
            
            $form->setData($request->getPost());
            
            if ($form->isValid()) {
                $owner->exchangeArray($form->getData());
                
                $role = new RoleModel($this->adapter);
                $role->read(['ROLENAME' => 'Owners']);
                
                $uuid = new Uuid();
                $owner->UUID = $uuid->value;
                
                $date = new \DateTime('now',new \DateTimeZone('EDT'));
                $today = $date->format('Y-m-d H:i:s');
                $owner->DATE_CREATED = $today;
                
                $owner->STATUS = $owner::ACTIVE_STATUS;
                $owner->assignRole($role->UUID);
                
                $owner->create();
                
                return $this->redirect()->toRoute('dog/owner', ['action' => 'update', 'uuid' => $owner->UUID]);
            }
        }
        
        return ([
            'form' => $form,
        ]);
    }
    
    public function updateAction()
    {
        $uuid = $this->params()->fromRoute('uuid',0);
        if (!$uuid) {
            return $this->redirect()->toRoute('dog/owner');
        }
        
        $owner = new OwnerModel($this->adapter);
        $owner->read(['UUID'=>$uuid]);
        
        $form = new OwnerForm();
        $form->bind($owner);
        $form->get('SUBMIT')->setAttribute('value', 'Update');
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($owner->getInputFilter());
            $form->setData($request->getPost());
            
            if ($form->isValid()) {
                $owner->update();
                
                $this->flashmessenger()->addSuccessMessage('Update Successful');
                
                $url = $this->getRequest()->getHeader('Referer')->getUri();
                return $this->redirect()->toUrl($url);
            }
            
        }
        
        //-- Return Dogs --//
        $dogs = $owner->getDogs();
        
        //-- BEGIN: Retrieve Annotations --//
        $annotation = new AnnotationModel($this->adapter);
        //$where = new Where(['TABLENAME' => 'dogs','PRIKEY' => $uuid]);
        $where = new Where([
            new Like('TABLENAME', 'users'),
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
        
        return [
            'uuid' => $uuid,
            'form' => $form,
            'annotations' => $notes,
            'annotations_prikey' => $uuid,
            'annotations_tablename' => 'users',
            'annotations_user' => '',
            'dogs' => $dogs,
        ];
    }
    
    public function deleteAction()
    {
        $uuid = $this->params()->fromRoute('uuid', 0);
        if (!$uuid) {
            return $this->redirect()->toRoute('dog/owner');
        }
        
        $user = new UserModel($this->adapter);
        $user->read(['UUID' => $uuid]);
        $user->delete();
        
        $this->flashmessenger()->addSuccessMessage('Owner deleted successfully.');
        
        return $this->redirect()->toRoute('dog/owner');
    }
    
    public function findAction()
    {
        $owner = new OwnerModel($this->adapter);
        $form = new OwnerSearchForm();
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            
            $data = $request->getPost();
            $form->setData($data);
            
            $search_string = NULL;
            if (stripos($data['LNAME'],'%')) {
                $search_string = $data['LNAME'];
            } else {
                $search_string = '%' . $data['LNAME'] . '%';
            }
            
            if ($form->isValid()) {
                $predicate = new Where();
                $predicate->like('LNAME', $search_string);
                $owners = $owner->fetchAll($predicate, ['LNAME']);
            }
        }
        
        return ([
            'owners' => $owners,
        ]);
    }
}