<?php 
namespace Dog\Controller;

use Annotation\Model\AnnotationModel;
use Dog\Form\OwnerSearchForm;
use Dog\Model\OwnerModel;
use User\Form\UserForm;
use User\Model\UserModel;
use Zend\Db\Adapter\AdapterAwareTrait;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Predicate\Like;
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
    
    public function updateAction()
    {
        $uuid = $this->params()->fromRoute('uuid',0);
        if (!$uuid) {
            return $this->redirect()->toRoute('dog/owner');
        }
        
        $owner = new OwnerModel($this->adapter);
        $owner->read(['UUID'=>$uuid]);
        
        $form = new UserForm();
        $form->bind($owner);
        $form->get('SUBMIT')->setAttribute('value', 'Update');
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($owner->getInputFilter());
            $form->setData($request->getPost());
            
            if ($form->isValid()) {
                $owner->update();
                
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
    
    public function findAction()
    {
        $owner = new OwnerModel($this->adapter);
        $form = new OwnerSearchForm();
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            
            $data = $request->getPost();
            $form->setData($data);
            
            if ($form->isValid()) {
                $predicate = new Where();
                $predicate->like('LNAME', '%' . $data['LNAME'] . '%');
                $owners = $owner->fetchAll($predicate, ['LNAME']);
            }
        }
        
        return ([
            'owners' => $owners,
        ]);
    }
}