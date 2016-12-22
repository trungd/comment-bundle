<?php

namespace dvtrung\CommentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Thread
 *
 * @ORM\Table(name="thread")
 * @ORM\Entity
 */
class Thread
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=255, unique=true)
     */
    private $code;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set code
     *
     * @param string $code
     *
     * @return Thread
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    public function getObjType() {
        if (!$this->getCode()) return '';
        $params = explode('-', $this->code);
        return isset($params[0]) ? $params[0] : '';
    }

    public function getObjId() {
        if (!$this->getCode()) return '';
        $params = explode('-', $this->code);
        return isset($params[1]) ? $params[1] : '';
    }

    public function setObjType($type) {
        $this->code = $type . '-' . $this->getObjId();
        return $this;
    }

    public function setObjId($id) {
        $this->code = $this->getObjType() . '-' . $id;
        return $this;
    }

    public function __toString() {
        return $this->code;
    }
}
