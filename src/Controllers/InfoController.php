<?php
/**
 * Created by PhpStorm.
 * User: Shinoa
 * Date: 03.09.2017
 * Time: 0:08
 */

namespace gsoft\Controllers;


use gsoft\Database\ClientMapper;
use gsoft\Database\ManagerMapper;

class InfoController extends PageController
{
    private $root;
    private $pdo;
    
    public function __construct($root, $pdo)
    {
        parent::__construct();
        $this->root = $root;
        $this->pdo = $pdo;
    }

    public function start()
    {
        $this->execute();
    }
    
    /**
     * Echoes json, containing object or FALSE.
     * @param $id
     */
    public function aboutClient($id)
    {
        $clientMapper = new ClientMapper($this->pdo);
        $client = $clientMapper->getClient($id);
        echo json_encode($client, JSON_UNESCAPED_UNICODE) ;
    }
    
    /**
     * Echoes json, containing object or FALSE.
     * @param $id
     */
    public function aboutManager($id)
    {
        $clientMapper = new ManagerMapper($this->pdo);
        $client = $clientMapper->getManager($id);
        echo json_encode($client, JSON_UNESCAPED_UNICODE) ;
    }
}