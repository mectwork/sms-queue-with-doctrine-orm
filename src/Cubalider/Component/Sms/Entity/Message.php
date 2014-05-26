<?php

namespace Cubalider\Component\Sms\Entity;

use Cubalider\Component\Mobile\Entity\MobileInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @author Yusliel Garcia <yuslielg@gmail.com>
 * @author Yosmany Garcia <yosmanyga@gmail.com>
 * @ORM\Entity
 */
class Message implements MessageInterface 
{
    /**
     * @var integer
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var MobileInterface
     * @ORM\ManyToOne(targetEntity="Cubalider\Component\Mobile\Entity\MobileInterface")
     */
    protected $receiver;

    /**
     * @var MobileInterface
     * @ORM\ManyToOne(targetEntity="Cubalider\Component\Mobile\Entity\MobileInterface")
     */
    protected $sender;

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    protected $text;

    /**
     * @var BulkInterface
     * @ORM\ManyToOne(targetEntity="Cubalider\Component\Sms\Entity\BulkInterface")
     */
    protected $bulk;

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param MobileInterface $receiver
     */
    public function setReceiver(MobileInterface $receiver)
    {
        $this->receiver = $receiver;
    }

    /**
     * @return MobileInterface
     */
    public function getReceiver()
    {
        return $this->receiver;
    }

    /**
     * @param MobileInterface $sender
     */
    public function setSender(MobileInterface $sender)
    {
        $this->sender = $sender;
    }

    /**
     * @return MobileInterface
     */
    public function getSender()
    {
        return $this->sender;
    }

    /**
     * @param string $text
     */
    public function setText($text)
    {
        $this->text = $text;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param BulkInterface $bulk
     */
    public function setBulk(BulkInterface $bulk)
    {
        $this->bulk = $bulk;
    }

    /**
     * @return BulkInterface
     */
    public function getBulk()
    {
        return $this->bulk;
    }

}