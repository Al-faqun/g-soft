<?php
namespace gsoft\Tests;

use gsoft\Database\UserMapper;
use gsoft\Entities\User;
use gsoft\Exceptions\DbException;
use PHPUnit\Framework\TestCase;

class UserMapperTest extends TestCase
{
    private $pdo;
    private $mapper;
    public function setUp()
    {
        $this->pdo = $GLOBALS['test_pdo'];
        $this->pdo->beginTransaction();
        $this->mapper = new UserMapper($this->pdo);
        
    }
    
    public function tearDown()
    {
        $this->pdo->rollBack();
    }
    
    public function testGetUser()
    {
        try {
            $result = $this->mapper->getUser(1);
            $this->assertInstanceOf(User::class, $result);
        } catch (DbException $e) {
            echo $e->getPrevious()->getMessage();
        }
    }
    
    public function testGetUserFail()
    {
        try {
            $result = $this->mapper->getUser(0);
            $this->assertFalse($result);
        } catch (DbException $e) {
            echo $e->getPrevious()->getMessage();
        }
    }
    
    public function testAddUser()
    {
        try {
            $result = $this->mapper->addUser('test user name');
            $this->assertInstanceOf(User::class, $result);
        } catch (DbException $e) {
            echo $e->getPrevious()->getMessage();
        }
    }
    
    public function testLastInsertedID()
    {
        try {
            $result = $this->mapper->addUser(null);
            $this->assertTrue($result->getId() > 0);
        } catch (DbException $e) {
            echo $e->getPrevious()->getMessage();
        }
    }
    
    public function testDeleteUser()
    {
        try {
            $user = $this->mapper->addUser('test user name');
            $result = $this->mapper->deleteUser($user->getId());
            $this->assertNotFalse($result);
        } catch (DbException $e) {
            echo $e->getPrevious()->getMessage();
        }
    }
    
    public function testDoesExist()
    {
        try {
            $result = $this->mapper->doesExist('admin');
            $this->assertNotFalse($result);
        } catch (DbException $e) {
            echo $e->getPrevious()->getMessage();
        }
    }
    
    public function testDoesExistFalse()
    {
        try {
            $result = $this->mapper->doesExist('nonexistant123456778');
            $this->assertFalse($result);
        } catch (DbException $e) {
            echo $e->getPrevious()->getMessage();
        }
    }
    
    public function testGetIdByName()
    {
        try {
            $id = $this->mapper->getIdByName('admin');
            $this->assertEquals(1, $id);
        } catch (DbException $e) {
            echo $e->getPrevious()->getMessage();
        }
    }
    
    public function testGetIdByNameFail()
    {
        try {
            $id = $this->mapper->getIdByName('nonononame');
            $this->assertFalse($id);
        } catch (DbException $e) {
            echo $e->getPrevious()->getMessage();
        }
    }
}
