<?php
namespace gsoft\Tests;

use gsoft\Database\LoginMapper;
use gsoft\Exceptions\DbException;
use PHPUnit\Framework\TestCase;

class LoginMapperTest extends TestCase
{
    private $pdo;
    private $mapper;
    public function setUp()
    {
        $this->pdo = $GLOBALS['test_pdo'];
        $this->pdo->beginTransaction();
        $this->mapper = new LoginMapper($this->pdo);
        
    }
    
    public function tearDown()
    {
        $this->pdo->rollBack();
    }
    
    public function testGetHash()
    {
        try {
            $result = $this->mapper->getHash(1);
            $this->assertNotFalse($result);
        } catch (DbException $e) {
            echo $e->getPrevious()->getMessage();
        }
    }
    
    public function testGetHashFail()
    {
        try {
            $result = $this->mapper->getHash(0);
            $this->assertFalse($result);
        } catch (DbException $e) {
            echo $e->getPrevious()->getMessage();
        }
    }
    
    public function testGetGetUserId()
    {
        try {
            $result = $this->mapper->getUserID(1);
            $this->assertNotFalse($result);
        } catch (DbException $e) {
            echo $e->getPrevious()->getMessage();
        }
    }
    
    public function testGetGetUserIdFail()
    {
        try {
            $result = $this->mapper->getUserID(0);
            $this->assertFalse($result);
        } catch (DbException $e) {
            echo $e->getPrevious()->getMessage();
        }
    }
    
    public function testAddLogin()
    {
        try {
            $result = $this->mapper->addLogin('test hash', 2);
            $this->assertNotFalse($result);
        } catch (DbException $e) {
            echo $e->getPrevious()->getMessage();
        }
    }
    
    public function testAddLoginFail()
    {
        $this->expectException(DbException::class);
        $result = $this->mapper->addLogin('test hash', 0);
        $this->assertFalse($result);
    }
    
    public function testChangeHash()
    {
        try {
            $id = $this->mapper->addLogin('test hash', 2);
            if ($id) {
                $result = $this->mapper->changeHash('updated hash', $id);
            } else {
                $result = false;
            }
            $this->assertTrue($result);
        } catch (DbException $e) {
            echo $e->getPrevious()->getMessage();
        }
    }
    
 
}
