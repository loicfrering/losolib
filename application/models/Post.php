<?php


/**
 * @Entity
 * @Table(name="post")
 */
class Application_Model_Post
{
    /**
     * @Column(name="id", type="integer")
     * @Id
     * @GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @Column(name="title", type="string", length=100)
     */
    private $title;

    /**
     * @Column(name="body", type="string")
     */
    private $body;

    /**
     * Get id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set title
     */
    public function setTitle($value)
    {
        $this->title = $value;
    }

    /**
     * Get title
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set body
     */
    public function setBody($value)
    {
        $this->body = $value;
    }

    /**
     * Get body
     */
    public function getBody()
    {
        return $this->body;
    }
}