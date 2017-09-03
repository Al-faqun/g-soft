<?php
namespace gsoft\Database;


use gsoft\Exceptions\DbException;
use gsoft\Entities\User;

class UserMapper
{
    private $pdo;
    
    /**
     * UserMapper constructor.
     * @param \PDO $pdo
     */
    function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }
    
    /**
     * @param $id
     * @return User|bool
     * @throws DbException
     */
    function getUser($id)
    {
        try {
            $sql = 'SELECT `username`, `usergroup` FROM `users` WHERE `id` = :id';
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
            $result = $stmt->execute();
            if (($result !== false) && ($stmt->rowCount() > 0)) {
                $assoc = $stmt->fetch(\PDO::FETCH_ASSOC);
                $result = new User($id, $assoc['username'], $assoc['usergroup']);
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
     * @param $username
     * @return bool
     * @throws DbException
     */
    public function getIdByName($username)
    {
        try {
            $sql = 'SELECT `id`, `usergroup` FROM `users` WHERE `username` = :username';
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':username', $username);
            $result = $stmt->execute();
            if (($result !== false) && ($stmt->rowCount() > 0)) {
                $assoc = $stmt->fetch(\PDO::FETCH_ASSOC);
                $result = (int)$assoc['id'];
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
     * @param $username
     * @return bool|User
     * @throws DbException
     */
    function addUser($username)
    {
        try {
            //no value means default: i.e., 'client'
            $sql = 'INSERT INTO `users`(`username`) VALUES (:username)';
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':username', $username);
            $result = $stmt->execute();
            if ( ($result !== false) AND ($this->lastInsertedId() !== 0) ) {
                $id = $this->lastInsertedId();
                $result = $this->getUser($id);
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
                throw new DbException('Ошибка при добавлении пользователя.', 0, $e);
            }
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
     * @param $userID
     * @return bool
     * @throws DbException
     */
    function deleteUser($userID)
    {
        try {
            $sql = 'DELETE FROM `users` WHERE `id` = :id';
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id', $userID, \PDO::PARAM_INT);
            $result = $stmt->execute();
        } catch (\PDOException $e) {
            throw new DbException('Ошибка при удалении пользователя.', 0, $e);
        }
        return $result;
    }
    
    /**
     * Checks whether user with such username exists
     * @param $username
     * @return bool
     * @throws DbException
     */
    public function doesExist($username)
    {
        try {
            $sql = 'SELECT `id` FROM `users` WHERE `username` = :name';
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':name', $username);
            $result = $stmt->execute();
            if ($result !== false) {
                $assoc = $stmt->fetch(\PDO::FETCH_ASSOC);
                $result = empty($assoc) ? false : true;
            }
        } catch (\PDOException $e) {
            throw new DbException('Ошибка при получении пользователя.', 0, $e);
        }
        return $result;
    }
}