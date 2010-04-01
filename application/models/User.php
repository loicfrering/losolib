<?php


/**
 * @Entity
 * @Table(name="user")
 */
class Application_Model_User
{
    /**
     * @Column(name="id", type="integer")
     * @Id
     * @GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @Column(name="firstname", type="string", length=50)
     */
    private $firstname;

    /**
     * @Column(name="lastname", type="string", length=50)
     */
    private $lastname;

    /**
     * @Column(name="email", type="string", length=100)
     */
    private $email;

    /**
     * Get id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set firstname
     */
    public function setFirstname($value)
    {
        $this->firstname = $value;
    }

    /**
     * Get firstname
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * Set lastname
     */
    public function setLastname($value)
    {
        $this->lastname = $value;
    }

    /**
     * Get lastname
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * Set email
     */
    public function setEmail($value)
    {
        $this->email = $value;
    }

    /**
     * Get email
     */
    public function getEmail()
    {
        return $this->email;
    }
}