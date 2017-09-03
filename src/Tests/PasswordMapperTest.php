<?php
namespace gsoft\Tests;

use gsoft\Database\PasswordMapper;
use gsoft\Exceptions\DbException;
use PHPUnit\Framework\TestCase;

class PasswordMapperTest extends TestCase
{
    private $pdo;
    private $mapper;
    public function setUp()
    {
        $this->pdo = $GLOBALS['test_pdo'];
        $this->pdo->beginTransaction();
        $this->mapper = new PasswordMapper($this->pdo);
        
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
    
    public function testAddHash()
    {
        try {
            $result = $this->mapper->addHash(3, 'test hash');
            $this->assertNotFalse($result);
        } catch (DbException $e) {
            echo $e->getPrevious()->getMessage();
        }
    }
    
    public function testAddHashFail()
    {
        try {
            $result = $this->mapper->addHash(0, 'test hash');
            $this->assertFalse($result);
        } catch (DbException $e) {
            echo $e->getPrevious()->getMessage();
        }
    }
    
    public function testAddHashExceptionFail()
    {
        $this->expectException(DbException::class);
        $result = $this->mapper->addHash(500000, 'test hash');
        $this->assertFalse($result);
    }
    
    public function testUpdateHash()
    {
        try {
            $result = $this->mapper->addHash(3, 'test hash');
            if ($result !== false) {
                $result = $this->mapper->updateHash(3, 'new test hash');
            }
            $this->assertNotFalse($result);
        } catch (DbException $e) {
            echo $e->getPrevious()->getMessage();
        }
    }
    
    public function testUpdateHashFail()
    {
        try {
            $result = $this->mapper->updateHash(0, 'new test hash');
            $this->assertFalse($result);
        } catch (DbException $e) {
            echo $e->getPrevious()->getMessage();
        }
    }
    
    public function testDeleteHash()
    {
        try {
            $result = $this->mapper->addHash(3, 'test hash');
            if ($result !== false) {
                $this->mapper->deleteHash(3);
                $result = $this->mapper->getHash(3);
            }
            $this->assertFalse($result);
        } catch (DbException $e) {
            echo $e->getPrevious()->getMessage();
        }
    }
    
    
    
    
}
