<?php
namespace gsoft\Entities;

/**
 * User entity for Db
 */
class User implements \JsonSerializable
{
    private $id = 0;
    private $username = '';
    private $usergroup = '';
    
    public function __construct($id, $username, $usergroup)
    {
        $this->setId($id);
        $this->setUsername($username);
        $this->setUsergroup($usergroup);
    }
    
    /**
     * @param int $id
     * @throws \Exception
     */
    public function setId($id)
    {
        $this->id = (int)$id;
    }
    
    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }
    
    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }
    
    /**
     * @param string $usergroup
     */
    public function setUsergroup($usergroup)
    {
        $this->usergroup = $usergroup;
    }
    
    /**
     * @return string
     */
    public function getUsergroup()
    {
        return $this->usergroup;
    }
    
    /**
     * To json_serialize private fields
     * @return array
     */
    public function jsonSerialize()
    {
        $vars = [
            'id' => $this->getId(),
            'usergroup' => $this->getUsergroup()
        ];
        
        return $vars;
    }
    
}