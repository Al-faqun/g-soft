<?php
namespace gsoft\Tests;

use gsoft\Database\ClientMapper;
use gsoft\Database\UserMapper;
use gsoft\Entities\Client;
use gsoft\Exceptions\DbException;
use PHPUnit\Framework\TestCase;

class ClientMapperTest extends TestCase
{
    private $pdo;
    private $mapper;
    public function setUp()
    {
        $this->pdo = $GLOBALS['test_pdo'];
        $this->pdo->beginTransaction();
        $this->mapper = new ClientMapper($this->pdo);
        
    }
    
    public function tearDown()
    {
        $this->pdo->rollBack();
    }
    
    public function testGetClient()
    {
        try {
            $result = $this->mapper->getClient(1);
            $this->assertInstanceOf(Client::class, $result);
        } catch (DbException $e) {
            echo $e->getPrevious()->getMessage();
        }
    }
    
    public function testGetClientFail()
    {
        try {
            $result = $this->mapper->getClient(0);
            $this->assertFalse($result);
        } catch (DbException $e) {
            echo $e->getPrevious()->getMessage();
        }
    }
    
    public function testAddClient()
    {
        try {
            $usermapper = new UserMapper($this->pdo);
            $client = new Client(0, 'some', 'some', 'some', 'some', 'some', 'some');
            $result = $this->mapper->addClient($usermapper, $client);
            $this->assertInstanceOf(Client::class, $result);
        } catch (DbException $e) {
            echo $e->getPrevious()->getMessage();
        }
    }
    
    public function testDoesExistEmail()
    {
        try {
            $result = $this->mapper->doesExistEmail('se@vas.org');
            $this->assertNotFalse($result);
        } catch (DbException $e) {
            echo $e->getPrevious()->getMessage();
        }
    }
    
    public function testDoesExistEmailFalse()
    {
        try {
            $result = $this->mapper->doesExistEmail('nonexistant123456778');
            $this->assertFalse($result);
        } catch (DbException $e) {
            echo $e->getPrevious()->getMessage();
        }
    }
}
