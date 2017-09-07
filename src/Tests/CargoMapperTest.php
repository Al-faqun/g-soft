<?php
namespace gsoft\Tests;

use gsoft\Entities\Cargo;
use gsoft\Database\CargoMapper;
use gsoft\Exceptions\DbException;
use PHPUnit\Framework\TestCase;

class CargoMapperTest extends TestCase
{
    private $pdo;
    private $mapper;
    public function setUp()
    {
        $this->pdo = $GLOBALS['test_pdo'];
        $this->pdo->beginTransaction();
        $this->mapper = new CargoMapper($this->pdo);
        
    }
    
    public function tearDown()
    {
        $this->pdo->rollBack();
    }
    
    public function testGetByID()
    {
        $result = $this->mapper->getByID(1);
        $this->assertInstanceOf(Cargo::class, $result);
    }
    
    public function testGetByIDFail()
    {
        $result = $this->mapper->getByID(0);
        $this->assertFalse($result);
    }
    
    public function testGetForClient()
    {
        try {
            $result = $this->mapper->getForClient(2, 5, 0);
            $this->assertNotFalse($result);
        } catch (DbException $e) {
            echo $e->getPrevious()->getMessage();
        }
    }
    
    public function testGetForClientWrongClientFail()
    {
        try {
            $result = $this->mapper->getForClient(0);
            $this->assertFalse($result);
        } catch (DbException $e) {
            echo $e->getPrevious()->getMessage();
        }
    }
    
    public function testGetForClientWrongLimitFail()
    {
        try {
            $result = $this->mapper->getForClient(1, 0);
            $this->assertFalse($result);
        } catch (DbException $e) {
            echo $e->getPrevious()->getMessage();
        }
    }
    
    public function testGetForManager()
    {
        try {
            $result = $this->mapper->getForManager(4); //be ware about whether this id is actually in db
            $this->assertNotFalse($result);
        } catch (DbException $e) {
            echo $e->getPrevious()->getMessage();
        }
    }
    
    public function testGetForManagerWrongClientFail()
    {
        try {
            $result = $this->mapper->getForManager(0); //be ware about whether this id is actually in db
            $this->assertFalse($result);
        } catch (DbException $e) {
            echo $e->getPrevious()->getMessage();
        }
    }
    
    public function testGetForManagerWrongLimitFail()
    {
        try {
            $result = $this->mapper->getForManager(1, 0); //be ware about whether this id is actually in db
            $this->assertFalse($result);
        } catch (DbException $e) {
            echo $e->getPrevious()->getMessage();
        }
    }
    
    public function testNewCargo()
    {
        try {
            $result = $this->mapper->newCargo('you must not see that', 1);
            $this->assertInstanceOf(Cargo::class, $result);
           
        } catch (DbException $e) {
            echo $e->getPrevious()->getMessage();
        }
    }

    public function testNewCargoForeignContraintFail()
    {
        $result = $this->mapper->newCargo('you must not see that', 0);
        $this->assertFalse($result);
    }
    
    public function testChangeManager()
    {
        try {
            $cargo = $this->mapper->newCargo('you must not see that', 1);
            $result = $this->mapper->changeManager($cargo->getId(), 5); //be ware about whether this id is actually in db
            $this->assertNotFalse($result);
            
        } catch (DbException $e) {
            echo $e->getPrevious()->getMessage();
        }
    }
    
    public function testChangeManagerFail()
    {
        try {
            $cargo = $this->mapper->newCargo('you must not see that', 1);
            $result = $this->mapper->changeManager($cargo->getId(), 0);
            $this->assertFalse($result);
            
        } catch (DbException $e) {
            echo $e->getPrevious()->getMessage();
        }
    }
    
    public function testChangeStatus()
    {
        try {
            $cargo = $this->mapper->newCargo('you must not see that', 1);
            $result = $this->mapper->changeStatus($cargo->getId(), 'finished');
            $this->assertNotFalse($result);
            
        } catch (DbException $e) {
            echo $e->getPrevious()->getMessage();
        }
    }
    
    public function testChangeDateArrival()
    {
        try {
            $cargo = $this->mapper->newCargo('you must not see that', 1);
            $datetime = new \DateTime('now');
            $result = $this->mapper->changeDateArrival($cargo->getId(), $datetime);
            $this->assertNotFalse($result);
            
        } catch (DbException $e) {
            echo $e->getPrevious()->getMessage();
        }
    }
    
    public function testChangeDateArrivalFail()
    {
        $this->expectException(\TypeError::class);
        $cargo = $this->mapper->newCargo('you must not see that', 1);
        $result = $this->mapper->changeDateArrival($cargo->getId(), '');
        $this->assertFalse($result);
    }
    
    public function testGetAwaiting1()
    {
        try {
            $result = $this->mapper->getAwaitingCargo(); //be ware about whether this id is actually in db
            $this->assertInternalType('array', $result);
        } catch (DbException $e) {
            echo $e->getPrevious()->getMessage();
        }
    }
    
    public function testGetAwaiting2()
    {
        try {
            $result = $this->mapper->getAwaitingCargo(10, 0); //be ware about whether this id is actually in db
            $this->assertInternalType('array', $result);
        } catch (DbException $e) {
            echo $e->getPrevious()->getMessage();
        }
    }
    
}
