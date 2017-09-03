<?php
/**
 * Created by PhpStorm.
 * User: Shinoa
 * Date: 29.08.2017
 * Time: 21:32
 */

namespace gsoft\Entities;


class Cargo
{
    private $id;
    private $container;
    private $clientID;
    private $clientName = '';
    private $manID;
    private $managerName = '';
    private $dateArrival;
    private $status;
    
    /**
     * Cargo constructor.
     * @param $id
     * @param $container
     * @param $clientID
     * @param $manID
     * @param $dateArrival
     * @param $status
     */
    public function __construct($id, $container, $clientID, $manID, $dateArrival, $status)
    {
        $this->setId($id);
        $this->setContainer($container);
        $this->setClientID($clientID);
        $this->setManID($manID);
        $this->setDateArrival($dateArrival);
        $this->setStatus($status);
    }
    
    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = (int)$id;
    }
    
    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * @param mixed $container
     */
    public function setContainer($container)
    {
        $this->container = $container;
    }
    
    /**
     * @return mixed
     */
    public function getContainer()
    {
        return $this->container;
    }
    
    /**
     * @param mixed $clientID
     */
    public function setClientID($clientID)
    {
        $this->clientID = (int)$clientID;
    }
    
    /**
     * @return mixed
     */
    public function getClientID()
    {
        return $this->clientID;
    }
    
    /**
     * @param mixed $clientName
     */
    public function setClientName($clientName)
    {
        $this->clientName = $clientName;
    }
    
    /**
     * @return mixed
     */
    public function getClientName()
    {
        return $this->clientName;
    }
    
    /**
     * @param mixed $manID
     */
    public function setManID($manID)
    {
        $this->manID = (int)$manID;
    }
    
    /**
     * @return mixed
     */
    public function getManID()
    {
        return $this->manID;
    }
    
    /**
     * @param mixed $managerName
     */
    public function setManagerName($managerName)
    {
        $this->managerName = $managerName;
    }
    
    /**
     * @return mixed
     */
    public function getManagerName()
    {
        return $this->managerName;
    }
    
    /**
     * @param mixed $dateArrival
     */
    public function setDateArrival($dateArrival)
    {
        if ( !($dateArrival instanceof \DateTime) ) {
            $this->dateArrival = new \DateTime($dateArrival);
        } else {
            $this->dateArrival = $dateArrival;
        }
        
    }
    
    /**
     * @return mixed
     */
    public function getDateArrival()
    {
        return $this->dateArrival->format('Y-m-d');
    }
    
    /**
     * @param mixed $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }
    
    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }
}