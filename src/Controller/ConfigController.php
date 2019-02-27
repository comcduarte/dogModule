<?php 
namespace Dog\Controller;

use Dog\Form\SubmitForm;
use Dog\Form\UploadFileForm;
use Dog\Model\DogCodeModel;
use Dog\Model\DogModel;
use Dog\Model\LicenseModel;
use Dog\Model\OwnerModel;
use Midnet\Model\Uuid;
use User\Model\RoleModel;
use User\Model\UserModel;
use Zend\Crypt\Password\Bcrypt;
use Zend\Db\Adapter\AdapterAwareTrait;
use Zend\Db\Sql\Delete;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Form\Form;
use Zend\Form\Element\Submit;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use RuntimeException;
use Annotation\Model\AnnotationModel;

class ConfigController extends AbstractActionController
{
    use AdapterAwareTrait;
    
    const KEY = 0;
    const LNAME = 1;
    const FNAME = 2;
    const TAG = 3;
    const ADDRESS = 4;
    const PHONE = 5;
    const CELL = 6;
    const DOG_NAME = 7;
    const BREED = 8;
    const COLOR = 9;
    const AGE = 10;
    const SEX = 11;
    const COD = 12;
    const FEE = 13;
    const RABIES_EXP = 14;
    const MEMO = 15;
    
    public function indexAction()
    {
        $importForm = new UploadFileForm();
        $importForm->initialize();
        $importForm->addInputFilter();
        
        $form = new SubmitForm();
        $form->initialize();
        
        
        $view = new ViewModel([
            'importForm' => $importForm,
            'form' => $form,
        ]);
        
        return $view;
    }
    
    public function importAction()
    {
        $form = new UploadFileForm();
        $form->initialize();
        $form->addInputFilter();
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $post = array_merge_recursive(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
                );
            $form->setData($post);
            if ($form->isValid()) {
                $data = $form->getData();
                $this->flashMessenger()->addSuccessMessage('Successful Upload: ' . $data['FILE']['tmp_name']);
                
                //-- Create global objects once for record creation --//
                $uuid = new Uuid();
                $bcrypt = new Bcrypt();
                $date = new \DateTime('now',new \DateTimeZone('EDT'));
                $today = $date->format('Y-m-d H:i:s');
                $year = $date->format('Y');
                $owner = new OwnerModel($this->adapter);
                $role = new RoleModel($this->adapter);
                $dog = new DogModel($this->adapter);
                $license = new LicenseModel($this->adapter);
                $code = new DogCodeModel($this->adapter);
                $annotation = new AnnotationModel($this->adapter);
                $empty = [];
                
                $role->read(['ROLENAME' => 'Owners']);
                
                
                //-- Begin reading file line by line --//
                $row = 0;
                if (($handle = fopen($data['FILE']['tmp_name'],"r")) !== FALSE) {
                    //-- Read record from file --//
                    while (($record = fgetcsv($handle, 1000, ",")) !== FALSE) {
                        /****************************************
                         * If KEY is null, this is a blank line, skip and continue.
                         ****************************************/
                        switch ($record[$this::KEY]) {
                            case 'KEY':
                            case '':
                                continue 2;
                                break;
                            default:
                                break;
                        }
                                                
                        /****************************************
                         *              Owner Model
                         ****************************************/
                        $owner->exchangeArray($empty);
                        
                        $owner->read([
                            'LNAME' => $record[$this::LNAME],
                            'FNAME' => $record[$this::FNAME],
                            'ADDR1' => $record[$this::ADDRESS],
                        ]);
                        
                        if (is_null($owner->UUID)) {
                            //-- Owner does not exist, create new one --//
                            $owner->UUID = $uuid->value;
                            $owner->USERNAME = substr($uuid->value, 0, 8);
                            $owner->PASSWORD = $bcrypt->create(substr($uuid->value, 24, 12));
                            $owner->DATE_CREATED = $today;
                            $owner->DATE_MODIFIED = $today;
                            
                            $owner->FNAME = $record[$this::FNAME];
                            $owner->LNAME = $record[$this::LNAME];
                            $owner->ADDR1 = $record[$this::ADDRESS];
                            $owner->CITY = "Middletown";
                            $owner->STATE = "CT";
                            $owner->ZIP = "06457";
                            $owner->STATUS = $owner::ACTIVE_STATUS;
                            
                            //-- Ensure properly formatted phone --//
                            $pattern = '/\((\d+)\) (\d+)-(\d+)/';
                            $replacement = '$1$2$3';
                            
                            $used_cell = TRUE;
                            if (!empty($record[$this::PHONE])) {
                                $owner->PHONE = preg_replace($pattern, $replacement, $record[$this::PHONE]);
                                $used_cell = FALSE;
                            } elseif (!empty($record[$this::CELL])) {
                                $owner->PHONE = preg_replace($pattern, $replacement, $record[$this::CELL]);
                                $used_cell = TRUE;
                            }
                            
                            $owner->create();
                            
                            if (!empty($record[$this::CELL]) && $used_cell === FALSE) {
                                $annotation->exchangeArray($empty);
                                $annotation->UUID = $uuid->generate()->value;
                                $annotation->TABLENAME = $owner->getTableName();
                                $annotation->USER = 'SYSTEM';
                                $annotation->PRIKEY = $owner->UUID;
                                $annotation->ANNOTATION = "Cell: " . $record[$this::CELL];
                                $annotation->STATUS = $annotation::ACTIVE_STATUS;
                                $annotation->DATE_CREATED = $today;
                                $annotation->DATE_MODIFIED = $today;
                                $annotation->create();
                            }
                            
                            $owner->assignRole([
                                'UUID' => $uuid->generate()->value,
                                'USER' => $owner->UUID,
                                'ROLE' => $role->UUID,
                            ]);
                        }
                        
                        /****************************************
                         *              Dog Model
                         ****************************************/
                        $dog->exchangeArray($empty);
                        
                        $rabies = new \DateTime($record[$this::RABIES_EXP],new \DateTimeZone('EDT'));
                        $rabies_formatted = $rabies->format('Y-m-d');
                        
                        $dog->read([
                            'NAME' => $record[$this::DOG_NAME],
                            'DATE_RABIESEXP' => $rabies_formatted,
                        ]);
                        
                        if (is_null($dog->UUID)) {
                            $dog->UUID = $uuid->generate()->value;
                            
                            $dog->NAME = $record[$this::DOG_NAME];
                            $dog->DESCRIPTION = $record[$this::COLOR] . "\r\n" . $record[$this::BREED];
                            $dog->DATE_RABIESEXP = $rabies_formatted;
                            $dog->DATE_CREATED = $today;
                            $dog->DATE_MODIFIED = $today;
                            $dog->DATE_BIRTH = $year - $record[$this::AGE] . "-01-01 00:00:00";
                            $dog->STATUS = $dog::ACTIVE_STATUS;
                            
                            //-- Determine Dog Sex --//
                            switch ($record[$this::SEX]) {
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
                        
                        /****************************************
                         *             License Model
                         ****************************************/
                        $license->exchangeArray($empty);
                        $code->exchangeArray($empty);
                        
                        if ($record[$this::TAG] != 0) {
                            $license->read(['TAG' => $record[$this::TAG]]);
                            
                            if (is_null($license->UUID)) {
                                $license->UUID = $uuid->generate()->value;
                                $license->DATE_CREATED = $today;
                                $license->DATE_MODIFIED = $today;
                                $license->TAG = $record[$this::TAG];
                                $license->YEAR = "2018";
                                $license->STATUS = $license::ACTIVE_STATUS;
                                $license->PAYMENT_STATUS = $license::ACTIVE_STATUS;
                                $license->DOG = $dog->UUID;
                                $license->FEE = $record[$this::FEE];
                                
                                
                                if (!is_null($record[$this::COD])) {
                                    $code->read(['CODE' => $record[$this::COD]]);
                                    if (!is_null($code->UUID)) {
                                        $license->assignCode($code->UUID);
                                    }
                                }
                                
                                $license->create();
                            }
                        }
                        
                        /****************************************
                         *            Temporary Break
                         ****************************************/
                        $row++;
                        if ($row >= 15) {
                            break;
                        }
                        
                    };
                    fclose($handle);
                    unlink($data['FILE']['tmp_name']);
                }
            } else {
                $this->flashMessenger()->addErrorMessage('Failure');
            }
            
        }
                
        return $this->redirect()->toRoute('dog/config');
    }
    
    public function clearAction()
    {
        $tables = [
            'annotations',
            'dogs',
            'dog_breeds',
            'dog_licenses',
            'dog_users',
            'license_codes',
            'users',
            'user_roles',
        ];
        
        foreach ($tables as $table) {
            $statement = $this->adapter->createStatement("TRUNCATE TABLE `" . $table . "`");
            $statement->execute();
        }
        
        $user = new UserModel($this->adapter);
        $user->FNAME = 'Administrator';
        $user->USERNAME = 'Admin';
        
        $bcrypt = new Bcrypt();
        $uuid = new Uuid();
        $user->PASSWORD = $bcrypt->create('admin');
        $user->UUID = $uuid->generate()->value;
        $user->STATUS = $user::ACTIVE_STATUS;
        
        $user->create();
        
        return $this->redirect()->toRoute('dog/config');
    }
    
    public function testfileAction()
    {
        
    }
    
    public function oldimportAction()
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
                    
                    
                    if ($record['TAG'] != 0) {
                        $result = $license->read(['TAG' => $record['TAG']]);
                        
                        if (is_null($result->UUID)) {
                            $uuid = new Uuid();
                            
                            
                            $license->UUID = $uuid->value;
                            $license->DATE_CREATED = $today;
                            $license->DATE_MODIFIED = $today;
                            $license->TAG = $record['TAG'];
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
                    
                    $delete = new Delete();
                    $delete->from('import')->where([
                        'LNAME' => $record['LNAME'],
                        'DOG NAME' => $record['DOG NAME'],
                        'AGE' => $record['AGE'],
                        'RABIES EXP' => $record['RABIES EXP']
                    ]);
                    $statement = $sql->prepareStatementForSqlObject($delete);
                    
                    try {
                        $statement->execute();
                    } catch (RuntimeException $e) {
                        echo $e;
                    }
                }
            }
        }
        
        return ([
            'form' => $form,
        ]);
    }
}