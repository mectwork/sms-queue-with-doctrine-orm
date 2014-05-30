<?php

namespace Cubalider\Component\Sms\Entity;

use Cubalider\Component\Mobile\Entity\MobileInterface;

/**
 * @author Yosmany Garcia <yosmanyga@gmail.com>
 */
interface MessageInterface
{
    /**
     * @param BulkInterface $bulk
     * @return void
     */
    public function setBulk(BulkInterface $bulk);

    /**
     * @return mixed
     */
    public function getBulk();

    /**
     * @param MobileInterface $sender
     * @return void
     */
    public function setSender(MobileInterface $sender);
    
    /**
     * @return MobileInterface
     */
    public function getSender();

    /**
     * @param MobileInterface $receiver
     * @return void
     */
    public function setReceiver(MobileInterface $receiver);
    
    /**
     * @return MobileInterface
     */
    public function getReceiver();

    /**
     * @param mixed $text
     * @return void
     */
    public function setText($text);
    
    /**
     * @return string
     */
    public function getText();
}