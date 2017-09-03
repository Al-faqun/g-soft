<?php
/**
 * Created by PhpStorm.
 * User: Shinoa
 * Date: 31.08.2017
 * Time: 18:08
 */

namespace gsoft\Entities;


class Manager extends User implements \JsonSerializable
{
    private $surname = '';
    private $name = '';
    private $email = '';
    private $tel = '';
    
    /**
     * Manager constructor.
     * @param $id
     * @param $surname
     * @param $name
     * @param $email
     * @param $tel
     * @param $username
     */
    public function __construct($id, $surname, $name, $email, $tel, $username)
    {
        parent::__construct($id, $username, 'manager');
        $this->setSurname($surname);
        $this->setName($name);
        $this->setEmail($email);
        $this->setTel($tel);
    }
    
    /**
     * @param string $surname
     */
    public function setSurname($surname)
    {
        $this->surname = $surname;
    }
    
    /**
     * @return string
     */
    public function getSurname()
    {
        return $this->surname;
    }
    
    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }
    
    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
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
    
    /**
     * To json_serialize private fields
     * @return array
     */
    public function jsonSerialize()
    {
        $parent = parent::jsonSerialize();
        $vars = [
            'surname' => $this->getSurname(),
            'name' => $this->getName(),
            'email' => $this->getEmail(),
            'tel' => $this->getTel()
        ];
    
        return array_merge($parent, $vars);
    }
    
}