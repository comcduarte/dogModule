<?php 
namespace Dog\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Db\Adapter\AdapterAwareTrait;
use Dog\Model\DogModel;
use Midnet\Model\Uuid;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Predicate\Like;
use Annotation\Model\AnnotationModel;
use User\Model\UserModel;

class DogController extends AbstractActionController
{
    use AdapterAwareTrait;
    
    public $form;
    
    public function indexAction()
    {
        $dog = new DogModel($this->adapter);
        $where = new Where();
        $dogs = $dog->fetchAll($where, ['NAME']);
        return ([
            'dogs' => $dogs,
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
                return $this->redirect()->toRoute('dog/dog');
            }
        }
        
        //-- Retrieve Annotations --//
        $annotation = new AnnotationModel($this->adapter);
        //$where = new Where(['TABLENAME' => 'dogs','PRIKEY' => $uuid]);
        $where = new Where([
            new Like('TABLENAME', 'dogs'),
            new Like('PRIKEY', $uuid),
        ]);
        $annotations = $annotation->fetchAll($where, ['DATE_CREATED']);
        
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
        
        
        return ([
            'annotations' => $notes,
            'form' => $this->form,
            'uuid' => $uuid,
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
}
