<?php 
namespace Dog\Form\Factory;

use Interop\Container\ContainerInterface;
use Dog\Form\DogForm;
use Dog\Model\DogModel;
use Zend\Db\Sql\Sql;
use RuntimeException;
use Zend\Db\Sql\Select as SqlSelect;
use Dog\Model\BreedModel;

class DogFormFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $form = new DogForm();
        $model = new DogModel();
        $form->setInputFilter($model->getInputFilter());
        $form->initialize();
        
        //-- Populate Breed Select Box --//
        $options = [];
        $adapter = $container->get('dog-model-primary-adapter');
        $breed = new BreedModel($adapter);
        $id_col = $breed->getPrimaryKey();
        $val_col = 'BREED';
        
        $sql = new Sql($adapter);
        
        $select = new SqlSelect();
        $select->from($breed->getTableName());
        $select->columns([$id_col => $id_col, $val_col => $val_col]);
        $select->order('BREED');
        
        $statement = $sql->prepareStatementForSqlObject($select);
        
        try {
            $resultSet = $statement->execute();
        } catch (RuntimeException $e) {
            return $e;
        }
        
        foreach ($resultSet as $object) {
            $options[$object[$id_col]] = $object[$val_col];
        }
                
        $form->get('BREED')->setOptions(['value_options' => $options]);
        
        
        
        return $form;
    }
}