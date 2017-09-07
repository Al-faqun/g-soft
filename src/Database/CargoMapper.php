<?php
/**
 * Created by PhpStorm.
 * User: Shinoa
 * Date: 29.08.2017
 * Time: 21:34
 */

namespace gsoft\Database;

use gsoft\Entities\Cargo;
use gsoft\Exceptions\DbException;

class CargoMapper
{
    private $pdo;
    private $lastCount = 0;
    
    function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }
    
    /**
     * @param $id
     * @return Cargo|bool
     * @throws \Exception
     */
    function getByID($id)
    {
        try {
            $sql = <<<'EOT'
                SELECT SQL_CALC_FOUND_ROWS
                `cargo`.`id` as 'id',
                `container`,
                `client_id`,
                `clients`.`company_name`  AS 'client_name',
                `man_id`,
                CONCAT(`managers`.`name`, ' ', `managers`.`surname`) AS 'manager_name',
                `date_arrival`,
                `status`
                FROM `cargo` LEFT JOIN `clients` ON `cargo`.`client_id` = `clients`.`id`
                LEFT JOIN `managers` ON `cargo`.`man_id` = `managers`.`id`
                WHERE `cargo`.`id` = :id
EOT;
                
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
            $result = $stmt->execute();
            if (($result !== false) && ($stmt->rowCount() > 0)) {
                $assoc = $stmt->fetch(\PDO::FETCH_ASSOC);
                //new object with fetched data
                $result = $this->convertToObject($assoc);
                //set optional fields
                $result->setClientName($assoc['client_name']);
                $result->setManagerName($assoc['manager_name']);
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
                throw new DbException('Ошибка при получении груза.', 0, $e);
            }
        }
        return $result;
    }
    
    /**
     * @param $clientID
     * @param int|string $limit If "all", then limit and offset would be ignored.
     * @param int $offset
     * @return array|bool
     * @throws DbException
     */
    function getForClient($clientID, $limit = 5, $offset = 0)
    {
        //these are whitelisted values so they can be inserted directly (they cannot go as prep parameters)
        $sortBy = '`cargo`.`id`';
        $order  = 'ASC';
        try {
            $cargo = array();
    
            $sql = <<<EOT
                SELECT SQL_CALC_FOUND_ROWS
                `cargo`.`id` as 'id',
                `container`,
                `client_id`,
                `clients`.`company_name`  AS 'client_name',
                `man_id`,
                CONCAT(`managers`.`surname`, ' ', `managers`.`name`) AS 'manager_name',
                `date_arrival`,
                `status`
                FROM `cargo`
                LEFT JOIN `clients` ON `cargo`.`client_id` = `clients`.`id`
                LEFT JOIN `managers` ON `cargo`.`man_id` = `managers`.`id`
                WHERE `clients`.`id` = :id
                ORDER BY $sortBy $order
EOT;
            if ($limit !== 'all') {
                $sql .= ' LIMIT :limit OFFSET :offset';
            }
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam('id', $clientID, \PDO::PARAM_INT);
            //if user indicate it wishes to get all rows at once
            if ($limit !== 'all') {
                $stmt->bindParam('limit', $limit, \PDO::PARAM_INT);
                $stmt->bindParam('offset', $offset, \PDO::PARAM_INT);
            }
            //if executed successfully
            if ( ($stmt->execute()) && ($stmt->rowCount() > 0) )  {
                while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                    //convert array of data to object
                    $fetchedCargo = $this->convertToObject($row);
                    //set optional fields
                    $fetchedCargo->setClientName($row['client_name']);
                    $fetchedCargo->setManagerName($row['manager_name']);
                    //save into array of cargo
                    $cargo[] = $fetchedCargo;
                }
            } else {
                $cargo = false;
            }
            //save total number of found rows for later use
            $this->lastCount = $this->foundRows();
        } catch (\PDOException $e) {
            if ($e->getCode() === '23000') {
                //'Integrity constraint violation: foreign key...'
                // - must not throw, because is predictable
                //instead return false
                $result = false;
            } else {
                throw new DbException('Ошибка при получении данных о грузах', 0, $e);
            }
        }
        return $cargo;
    }
    
    /**
     * @param $managerID
     * @param int $limit
     * @param int $offset
     * @return array|bool
     * @throws DbException
     */
    function getForManager($managerID, $limit = 5, $offset = 0)
    {
        //these are whitelisted values so they can be inserted directly (they cannot go as prep parameters)
        $sortBy = '`cargo`.`id`';
        $order  = 'ASC';
        try {
            $cargo = array();
            
            $sql = <<<EOT
                SELECT SQL_CALC_FOUND_ROWS
                `cargo`.`id` as 'id',
                `container`,
                `client_id`,
                `clients`.`company_name`  AS 'client_name',
                `man_id`,
                CONCAT(`managers`.`name`, ' ', `managers`.`surname`) AS 'manager_name',
                `date_arrival`,
                `status`
                FROM `cargo`
                LEFT JOIN `clients` ON `cargo`.`client_id` = `clients`.`id`
                LEFT JOIN `managers` ON `cargo`.`man_id` = `managers`.`id`
                WHERE `managers`.`id` = :id
                ORDER BY $sortBy $order
EOT;
            if ($limit !== 'all') {
                $sql .= ' LIMIT :limit OFFSET :offset';
            }
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam('id', $managerID, \PDO::PARAM_INT);
            //if user indicate it wishes to get all rows at once
            if ($limit !== 'all') {
                $stmt->bindParam('limit', $limit, \PDO::PARAM_INT);
                $stmt->bindParam('offset', $offset, \PDO::PARAM_INT);
            }
            //if executed successfully
            if ( ($stmt->execute()) && ($stmt->rowCount() > 0) )  {
                //save total number of found rows for later use
                $this->lastCount = $this->foundRows();
                while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                    //convert array of data to object
                    $fetchedCargo = $this->convertToObject($row);
                    //set optional fields
                    $fetchedCargo->setClientName($row['client_name']);
                    $fetchedCargo->setManagerName($row['manager_name']);
                    //save into array of cargo
                    $cargo[] = $fetchedCargo;
                }
            } else {
                $cargo = false;
            }
        } catch (\PDOException $e) {
            if ($e->getCode() === '23000') {
                //'Integrity constraint violation: foreign key...'
                // - must not throw, because is predictable
                //instead return false
                $result = false;
            } else {
                throw new DbException('Ошибка при получении данных о грузах', 0, $e);
            }
        }
        return $cargo;
    }
    
    function getAwaitingCargo($limit = 5, $offset = 0)
    {
        //these are whitelisted values so they can be inserted directly (they cannot go as prep parameters)
        $sortBy = '`cargo`.`id`';
        $order  = 'ASC';
        try {
            $cargo = array();
        
            $sql = <<<EOT
                SELECT SQL_CALC_FOUND_ROWS
                `cargo`.`id` as 'id',
                `container`,
                `client_id`,
                `clients`.`company_name`  AS 'client_name',
                `man_id`,
                CONCAT(`managers`.`name`, ' ', `managers`.`surname`) AS 'manager_name',
                `date_arrival`,
                `status`
                FROM `cargo`
                LEFT JOIN `clients` ON `cargo`.`client_id` = `clients`.`id`
                LEFT JOIN `managers` ON `cargo`.`man_id` = `managers`.`id`
                WHERE `status` = 'awaiting' AND `man_id` IS NULL
                ORDER BY $sortBy $order
EOT;
            if ($limit !== 'all') {
                $sql .= ' LIMIT :limit OFFSET :offset';
            }
            
            $stmt = $this->pdo->prepare($sql);
            if ($limit !== 'all') {
                $stmt->bindParam('limit', $limit, \PDO::PARAM_INT);
                $stmt->bindParam('offset', $offset, \PDO::PARAM_INT);
            }
            //if executed successfully
            if ( ($stmt->execute()) && ($stmt->rowCount() > 0) )  {
                while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                    //convert array of data to object
                    $fetchedCargo = $this->convertToObject($row);
                    //set optional fields
                    $fetchedCargo->setClientName($row['client_name']);
                    $fetchedCargo->setManagerName($row['manager_name']);
                    //save into array of cargo
                    $cargo[] = $fetchedCargo;
                }
            } else {
                $cargo = false;
            }
            //save total number of found rows for later use
            $this->lastCount = $this->foundRows();
        } catch (\PDOException $e) {
            if ($e->getCode() === '23000') {
                //'Integrity constraint violation: foreign key...'
                // - must not throw, because is predictable
                //instead return false
                $result = false;
            } else {
                throw new DbException('Ошибка при получении данных о грузах', 0, $e);
            }
        }
        return $cargo;
    }
    
    /**
     * @param $container
     * @param $clientID
     * @return bool|Cargo
     * @throws DbException
     */
    function newCargo($container, $clientID)
    {
        try {
            $sql = <<<'EOT'
            INSERT INTO `cargo`
            (`container`, `client_id`)
            VALUES (:container, :client_id)
EOT;

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':client_id', $clientID, \PDO::PARAM_INT);
            $stmt->bindParam(':container', $container);
            $result = $stmt->execute();
            
            if ( ($result !== false) AND ($this->lastInsertedId() !== 0) ) {
                $id = $this->lastInsertedId();
                $result = $this->getByID($id);
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
                throw new DbException('Ошибка при добавлении груза.', 0, $e);
            }
        }
        return $result;
    }
    
    /**
     *Use only  next expr after 'insert'.
     *
     * @return int id of last inserted ID or 0 if cannot retrieve
     */
    public function lastInsertedId()
    {
        return (int)$this->pdo->lastInsertId();
    }
    
    /**
     * @param $cargoID
     * @param $managerID
     * @return bool
     * @throws DbException
     */
    function changeManager($cargoID, $managerID)
    {
        try {
            $sql = 'UPDATE `cargo` SET `man_id` = :man_id WHERE `id` = :id';
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id', $cargoID, \PDO::PARAM_INT);
            $stmt->bindParam(':man_id', $managerID, \PDO::PARAM_INT);
            $result = $stmt->execute();
        } catch (\PDOException $e) {
            if ($e->getCode() === '23000') {
                //'Integrity constraint violation: foreign key...'
                // - must not throw, because is predictable
                //instead return false
                $result = false;
            } else {
                throw new DbException('Ошибка при изменении менеджера груза.', 0, $e);
            }
        }
        return $result;
    }
    
    /**
     * @param $cargoID
     * @param $status
     * @return bool
     * @throws DbException
     */
    function changeStatus($cargoID, $status)
    {
        try {
            $sql = 'UPDATE `cargo` SET `status` = :status WHERE `id` = :id';
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id', $cargoID, \PDO::PARAM_INT);
            $stmt->bindParam(':status', $status);
            $result = $stmt->execute();
        } catch (\PDOException $e) {
            if ($e->getCode() === '23000') {
                //'Integrity constraint violation: foreign key...'
                // - must not throw, because is predictable
                //instead return false
                $result = false;
            } else {
                throw new DbException('Ошибка при изменении даты прибытия груза.', 0, $e);
            }
        }
        return $result;
    }
    
    /**
     * @param $cargoID
     * @param $dateArrival
     * @return bool
     * @throws DbException
     */
    function changeDateArrival($cargoID, \DateTime $dateArrival)
    {
        try {
            $sql = 'UPDATE `cargo` SET `date_arrival` = :date_arrival WHERE `id` = :id';
            $stmt = $this->pdo->prepare($sql);
            $date = $dateArrival->format('Y-m-d');
            $stmt->bindParam(':id', $cargoID, \PDO::PARAM_INT);
            $stmt->bindParam(':date_arrival', $date);
            $result = $stmt->execute();
        } catch (\PDOException $e) {
            if ($e->getCode() === '23000') {
                //'Integrity constraint violation: foreign key...'
                // - must not throw, because is predictable
                //instead return false
                $result = false;
            } else {
                throw new DbException('Ошибка при изменении даты прибытия груза груза.', 0, $e);
            }
        }
        return $result;
    }
    
    /**
     * @param $cargoID
     * @param $status
     * @param \DateTime $dateArrival
     * @return bool
     * @throws DbException
     */
    function changeStatusAndDateArrival($cargoID, $status, \DateTime $dateArrival)
    {
        try {
            $sql = 'UPDATE `cargo` SET `date_arrival` = :date_arrival, `status` = :status WHERE `id` = :id';
            $stmt = $this->pdo->prepare($sql);
            $date = $dateArrival->format('Y-m-d');
            $stmt->bindParam(':id', $cargoID, \PDO::PARAM_INT);
            $stmt->bindParam(':date_arrival', $date);
            $stmt->bindParam(':status', $status);
            $result = $stmt->execute();
        } catch (\PDOException $e) {
            if ($e->getCode() === '23000') {
                //'Integrity constraint violation: foreign key...'
                // - must not throw, because is predictable
                //instead return false
                $result = false;
            } else {
                throw new DbException('Ошибка при изменении менеджера груза.', 0, $e);
            }
        }
        return $result;
    }
    
    /**
     * @return int|bool
     * @throws \Exception
     */
    public function foundRows()
    {
        try {
            //initialize default value of result
            $count = false;
            //get sql
            $sql = 'SELECT FOUND_ROWS()';
            $stmt = $this->pdo->prepare($sql);
            if ($stmt->execute()) {
                //if get nothing from DB
                if ($stmt->rowCount() == 0) {
                    $count = false;
                }
                $row = $stmt->fetch(\PDO::FETCH_NUM);
                //on success we get only one item from DB
                $count = $row[0];
                
            } else {
                $count = false;
            }
        } catch (\PDOException $e) {
            throw new DbException('Ошибка при получении числа найденных записей', 0, $e);
        }
        
        return $count;
    }
    
    /**
     * Gets number of rows, affected by last select query, !without! WHERE clauses.
     * @return mixed|bool number of rows on success, FALSE if failure.
     */
    public function getEntriesCount()
    {
        return $this->lastCount;
    }
    
    /**
     * Converts arra from db to object
     * @param $row
     * @return Cargo|bool
     * @throws \Exception
     */
    private function convertToObject($row)
    {
        $required = array('id' => 1, 'container' => 2, 'client_id' => 3, 'man_id' => 4, 'date_arrival' => 5,
            'status' => 6);
        if (( !is_array($row) )  || ( !empty(array_diff_key($required, $row)) )) {
            return false;
        }
    
        $result = new Cargo(
            $row['id'],
            $row['container'],
            $row['client_id'],
            $row['man_id'],
            $row['date_arrival'],
            $row['status']
        );
        return $result;
    }
    
}