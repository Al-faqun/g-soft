<?php
/**
 * Created by PhpStorm.
 * User: Shinoa
 * Date: 31.08.2017
 * Time: 18:08
 */

namespace gsoft\Database;


use gsoft\Entities\Manager;
use gsoft\Exceptions\DbException;

class ManagerMapper
{
    private $pdo;
    
    /**
     * ManagerMapper constructor.
     * @param \PDO $pdo
     */
    function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }
    
    
    function getManager($id)
    {
        try {
            $sql = <<<EOT
            SELECT `managers`.`id`, `users`.`username`, `surname`, `name`, `email`, `tel`
            FROM `managers`
            JOIN `users` ON `managers`.`id` = `users`.`id`
            WHERE `managers`.`id` = :id
EOT;
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
            $result = $stmt->execute();
            if (($result !== false) && ($stmt->rowCount() > 0)) {
                $assoc = $stmt->fetch(\PDO::FETCH_ASSOC);
                $result = self::arrayToObject($assoc);
            } else {
                $result = false;
            }
        } catch (\PDOException $e) {
            if ($e->getCode() === '23000') {
                //'Integrity constraint violation: foreign key...'
                // - must not throw, because is predictable
                //instead return false
                $result = false;
            } else {
                throw new DbException('Ошибка при получении менеджера.', 0, $e);
            }
        }
        return $result;
    }
    
    /**
     * Row of data from db into entity object.
     * @param $row
     * @return bool|Manager
     */
    static function arrayToObject($row)
    {
        $required = array('id' => 1, 'surname' => 2, 'name' => 3, 'email' => 4, 'tel' => 5, 'username' => 6);
        if (( !is_array($row) )  || ( !empty(array_diff_key($required, $row)) )) {
            return false;
        }
        
        $result = new Manager(
            $row['id'],
            $row['surname'],
            $row['name'],
            $row['email'],
            $row['tel'],
            $row['username']
        );
        return $result;
    }
    
    /**
     * @param Manager $manager
     * @return array
     */
    static function objectToArray(Manager $manager)
    {
        $result = array();
        $result['id']           = $manager->getId();
        $result['surname']      = $manager->getSurname();
        $result['name']         = $manager->getName();
        $result['email']        = $manager->getEmail();
        $result['tel']          = $manager->getTel();
        $result['username']     = $manager->getUsername();
        return $result;
    }
}