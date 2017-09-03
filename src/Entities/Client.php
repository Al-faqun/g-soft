<?php
/**
 * Created by PhpStorm.
 * User: Shinoa
 * Date: 31.08.2017
 * Time: 15:15
 */

namespace gsoft\Entities;

/**
 * Client entity for Db
 * @package gsoft\Entities
 */
class Client extends User implements \JsonSerializable
{
    private $companyName = '';
    private $inn = '';
    private $address = '';
    private $email = '';
    private $tel = '';
    
    /**
     * Client constructor.
     * @param $id
     * @param $companyName
     * @param $inn
     * @param $address
     * @param $email
     * @param $tel
     * @param $username
     */
    public function __construct($id, $companyName, $inn, $address, $email, $tel, $username)
    {
        parent::__construct($id, $username, 'client');
        $this->setCompanyName($companyName);
        $this->setInn($inn);
        $this->setAddress($address);
        $this->setEmail($email);
        $this->setTel($tel);
    }
    
    /**
     * @param string $companyName
     */
    public function setCompanyName($companyName)
    {
        $this->companyName = $companyName;
    }
    
    /**
     * @return string
     */
    public function getCompanyName()
    {
        return $this->companyName;
    }
    
    /**
     * @param string $inn
     */
    public function setInn($inn)
    {
        $this->inn = $inn;
    }
    
    /**
     * @return string
     */
    public function getInn()
    {
        return $this->inn;
    }
    
    /**
     * @param string $address
     */
    public function setAddress($address)
    {
        $this->address = $address;
    }
    
    /**
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }
    
    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }
    
    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }
    
    /**
     * @param string $tel
     */
    public function setTel($tel)
    {
        $this->tel = $tel;
    }
    
    /**
     * @return string
     */
    public function getTel()
    {
        return $this->tel;
    }
    
    public static function fromArray($array)
    {
        $client = new Client(
            0,
            $array['company_name'],
            $array['inn'],
            $array['address'],
            $array['email'],
            $array['tel'],
            $array['username']
        );
        return $client;
    }
    
    /**
     * To json_serialize private fields
     * @return array
     */
    public function jsonSerialize()
    {
        $parent = parent::jsonSerialize();
        $vars = [
            'companyName' => $this->getCompanyName(),
            'inn' => $this->getInn(),
            'address' => $this->getAddress(),
            'email' => $this->getEmail(),
            'tel' => $this->getTel()
        ];
        
        return array_merge($parent, $vars);
    }
}