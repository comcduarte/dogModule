<?php 
namespace Dog\Form\Factory;

use Dog\Form\DogForm;
use Dog\Model\DogModel;
use Interop\Container\ContainerInterface;
use Midnet\Model\Uuid;

class DogFormFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $uuid = new Uuid();
        
        $form = new DogForm($uuid->value);
        $model = new DogModel();
        $form->setInputFilter($model->getInputFilter());
        
        
        //-- Populate Breed Select Box --//
//         $options = [];
        $adapter = $container->get('dog-model-primary-adapter');
//         $breed = new BreedModel($adapter);
//         $id_col = $breed->getPrimaryKey();
//         $val_col = 'BREED';
        
//         $sql = new Sql($adapter);
        
//         $select = new SqlSelect();
//         $select->from($breed->getTableName());
//         $select->columns([$id_col => $id_col, $val_col => $val_col]);
//         $select->order('BREED');
        
//         $statement = $sql->prepareStatementForSqlObject($select);
        
//         try {
//             $resultSet = $statement->execute();
//         } catch (RuntimeException $e) {
//             return $e;
//         }
        
//         foreach ($resultSet as $object) {
//             $options[$object[$id_col]] = $object[$val_col];
//         }
        $form->setDbAdapter($adapter);  
        
        $form->initialize();
//         $form->get('BREED')->setOptions(['value_options' => $options]);
        
        
        
        return $form;
    }
}