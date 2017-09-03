<?php
/**
 * Created by PhpStorm.
 * User: Shinoa
 * Date: 31.08.2017
 * Time: 2:25
 */

namespace gsoft\Database;


use gsoft\Entities\Client;
use gsoft\Exceptions\DbException;

class ClientMapper
{
    private $pdo;
    
    /**
     * Clientmapper constructor.
     * @param \PDO $pdo
     */
    function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }
    
    /**
     * @param $id
     * @return bool|Client
     * @throws DbException
     */
    function getClient($id)
    {
        try {
            $sql = <<<EOT
            SELECT `clients`.`id`, `users`.`username`, `company_name`, `inn`, `address`, `email`, `tel`
            FROM `clients`
            JOIN `users` ON `clients`.`id` = `users`.`id`
            WHERE `clients`.`id` = :id
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
                throw new DbException('Ошибка при получении пользователя.', 0, $e);
            }
        }
        return $result;
    }
    
    /**
     * Add client data to both `user` and `client` tables.
     * @param UserMapper $usermapper
     * @param Client $client
     * @return Client|bool returns Client on success, false on failure, or throws exception
     * @throws DbException
     */
    function addClient(UserMapper $usermapper, Client $client)
    {
        //ATTENTION:
        //due to inability of PDO to containt nested transaction, this example was modified for use in tests and else
        //it's a crunch that must be deleted when instead of simple pdo there will be modified class with nested transactions enabled
        $trEnabled = false;
        //inserting new client consist of two parts:
        //user table and client table (in that order, because of FK)
        try {
            //because we insert into more than one table,use transactions for security
            if (!$this->pdo->inTransaction()) {
                $trEnabled = true;
                $this->pdo->beginTransaction();
            }
            
            //insert into Users table
            $user = $usermapper->addUser($client->getUsername());
            if ($user !== false) {
                $client->setId($user->getId());
            } else {
                //if insertion failed - exit with exception, because operation is compromised
                throw new DbException('Ошибка при добавлении пользователя.');
            }
            
            //insert into Client table
            //be ware, Client object currently posses true ID
            $stmt = $this->prepareStatement($client);
            $result = $stmt->execute();
            if ( ($result !== false) ) {
                //because client.id is NOT AI field, we must fetch it's ID by other means
                $id = $client->getId();
                $result = $this->getClient($id);
            }
            
            //if successful - commit transaction
            //beware of forementioned CRUNCH in effect here
            if ($trEnabled) {
                $this->pdo->commit();
            }
        } catch (\PDOException $e) {
            if ($trEnabled) {
                //if error - rollback to safe state
                $this->pdo->rollBack();
            }
            if ($e->getCode() === '23000') {
                //'Integrity constraint violation: foreign key...'
                // - must not throw, because it is predictable
                //instead return false
                $result = false;
            } else {
                throw new DbException('Ошибка при добавлении пользователя.', 0, $e);
            }
        }
        return $result;
    }
    
    /**
     * Checks whether client with such email exists.
     * @param $email
     * @return bool
     * @throws DbException
     */
    public function doesExistEmail($email)
    {
        try {
            $sql = 'SELECT `email` FROM `clients` WHERE `email` = :email';
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':email', $email);
            $result = $stmt->execute();
            if ($result !== false) {
                $assoc = $stmt->fetch(\PDO::FETCH_ASSOC);
                $result = empty($assoc) ? false : true;
            }
        } catch (\PDOException $e) {
            throw new DbException('Ошибка при получении электронной почты клиента.', 0, $e);
        }
        return $result;
    }
    
    /**
     * Use only next expression after insert.
     *
     * @return int id of last inserted ID or 0 if cannot retrieve
     */
    public function lastInsertedId()
    {
        return (int)$this->pdo->lastInsertId();
    }
    
    
    /**
     * Returns prepared statement with binded params.
     * @param Client $client
     * @param string $typeOfStatement  'insert'
     * @return \PDOStatement
     * @throws DbException
     */
    function prepareStatement(Client $client, $typeOfStatement = 'insert')
    {
        try {
            switch ( strtolower($typeOfStatement) ) {
                case 'insert':
                    $sql = <<<EOT
            INSERT INTO `clients`(`id`, `company_name`, `inn`, `address`, `email`, `tel`)
            VALUES (:id, :company_name, :inn, :address, :email, :tel)
EOT;
                    break;
                case 'update':
                    break;
                default:
                    throw new DbException('Incorrect type of statement');
            }
            $stmt = $this->pdo->prepare($sql);
        
            $id          = $client->getId();
            $companyName = $client->getCompanyName();
            $inn         = $client->getInn();
            $address     = $client->getAddress();
            $email       = $client->getEmail();
            $tel         = $client->getTel();
        
            $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
            $stmt->bindParam(':company_name', $companyName);
            $stmt->bindParam(':inn', $inn);
            $stmt->bindParam(':address', $address);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':tel', $tel);
        } catch (\PDOException $e) {
            throw new DbException('Ошибка при добавлении данных клиента', 0, $e);
        }
        return $stmt;
    }
    
    /**
     * Row of data from db into entity object.
     * @param $row
     * @return bool|Client
     */
    static function arrayToObject($row)
    {
        $required = array('id' => 1, 'company_name' => 2, 'inn' => 3, 'address' => 4, 'email' => 5,
            'tel' => 6, 'username' => 7);
        if (( !is_array($row) )  || ( !empty(array_diff_key($required, $row)) )) {
            return false;
        }
    
        $result = new Client(
            $row['id'],
            $row['company_name'],
            $row['inn'],
            $row['address'],
            $row['email'],
            $row['tel'],
            $row['username']
        );
        return $result;
    }
    
    /**
     * @param Client $client
     * @return array
     */
    static function objectToArray(Client $client)
    {
        $result = array();
        $result['id']           = $client->getId();
        $result['company_name'] = $client->getCompanyName();
        $result['inn']          = $client->getInn();
        $result['address']      = $client->getAddress();
        $result['email']        = $client->getEmail();
        $result['tel']          = $client->getTel();
        $result['username']     = $client->getUsername();
        return $result;
    }
    
}