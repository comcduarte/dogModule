<?php 
namespace Dog\Controller;

use Dog\Form\ReportForm;
use Dog\Model\ReportModel;
use Midnet\Model\Uuid;
use Zend\Db\Adapter\AdapterAwareTrait;
use Zend\Mvc\Controller\AbstractActionController;
use RuntimeException;
use Zend\Db\Sql\Where;

class ReportController extends AbstractActionController
{
    use AdapterAwareTrait;
    
    public function indexAction()
    {
        $report = new ReportModel($this->adapter);
        $reports = $report->fetchAll(new Where(), ['NAME']);
        
        return ([
            'reports' => $reports,
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
        $uuid = $this->params()->fromRoute('uuid',0);
        if (!$uuid) {
            return $this->redirect()->toRoute('dog/report');
        }
        
        $report = new ReportModel($this->adapter);
        $report->read(['UUID' => $uuid]);
        
        $statement = $this->adapter->createStatement($report->CODE);
        
        try {
            $data = $statement->execute();
        } catch (RuntimeException $e) {
            return $e;
        }
        
        return ([
            'data' => $data,
            'view' => $report->VIEW,
        ]);
        
    }
}