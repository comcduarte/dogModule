<?php 
namespace Dog\Controller;

use Dog\Form\ReportForm;
use Dog\Model\ReportModel;
use Midnet\Model\Uuid;
use Zend\Db\Adapter\AdapterAwareTrait;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\DbSelect;
use RuntimeException;

class ReportController extends AbstractActionController
{
    use AdapterAwareTrait;
    
    public function indexAction()
    {
        $report = new ReportModel($this->adapter);
        $where = new Where();
        
        $select = new Select();
        $select->from($report->getTableName());
        $select->where($where);
        $select->order(['NAME']);
        
        $paginator = new Paginator(new DbSelect($select, $this->adapter));
        $paginator->setDefaultScrollingStyle('All');
        
        $count = $this->params()->fromRoute('count', 15);
        
        $paginator->setCurrentPageNumber($this->params()->fromRoute('page', 1));
        $paginator->setItemCountPerPage($count);
        
        return ([
            'reports' => $paginator,
        ]);
    }
    
    public function createAction()
    {
        $request = $this->request;
        
        $form = new ReportForm();
        $form->initialize();
        
        if ($request->isPost()) {
            $form->setData($request->getPost());
            
            if ($form->isValid()) {
                $model = new ReportModel($this->adapter);
                $model->exchangeArray($form->getData());
                
                $uuid = new Uuid();
                $model->UUID = $uuid->value;
                $model->create();
                
                return $this->redirect()->toRoute('dog/report', ['action' => 'update', 'uuid' => $model->UUID]);
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
            return $this->redirect()->toRoute('dog/report');
        }
        
        $model = new ReportModel($this->adapter);
        $model->read(['UUID' => $uuid]);
        
        $form = new ReportForm();
        $form->initialize();
        
        $form->bind($model);
        $form->get('SUBMIT')->setAttribute('value', 'Update');
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());
            
            if ($form->isValid()) {
                $model->update();
                
                return $this->redirect()->toRoute('dog/report', ['action' => 'update', 'uuid' => $model->UUID]);
            }
        }
        
        return ([
            'form' => $form,
            'uuid' => $uuid,
        ]);
    }
    
    public function viewAction()
    {
        $this->layout('layout/report');
        
        $uuid = $this->params()->fromRoute('uuid',0);
        if (!$uuid) {
            return $this->redirect()->toRoute('dog/report');
        }
        
        $report = new ReportModel($this->adapter);
        $report->read(['UUID' => $uuid]);
        
        $statement = $this->adapter->createStatement($report->CODE);
        
        try {
            $resultSet = new ResultSet();
            $data = $statement->execute();
            $resultSet->initialize($data);
        } catch (RuntimeException $e) {
            return $e;
        }
        
        
        
        return ([
            'data' => $resultSet->toArray(),
            'view' => $report->VIEW,
        ]);
        
    }
}