<?php
namespace gsoft\Tests;

use gsoft\Database\ManagerMapper;
use gsoft\Entities\Manager;
use gsoft\Exceptions\DbException;
use PHPUnit\Framework\TestCase;

class ManagerMapperTest extends TestCase
{
    private $pdo;
    private $mapper;
    public function setUp()
    {
        $this->pdo = $GLOBALS['test_pdo'];
        $this->pdo->beginTransaction();
        $this->mapper = new ManagerMapper($this->pdo);
        
    }
    
    public function tearDown()
    {
        $this->pdo->rollBack();
    }
    
    public function testGetManager()
    {
        try {
            $result = $this->mapper->getManager(5);
            $this->assertInstanceOf(Manager::class, $result);
        } catch (DbException $e) {
            echo $e->getPrevious()->getMessage();
        }
    }
    
    public function testGetManagerFail()
    {
        try {
            $result = $this->mapper->getManager(0);
            $this->assertFalse($result);
        } catch (DbException $e) {
            echo $e->getPrevious()->getMessage();
        }
    }
}
