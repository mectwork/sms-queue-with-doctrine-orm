<?php

namespace Muchacuba\Component\Sms\Entity;

use Muchacuba\Component\Mobile\Entity\MobileInterface;

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
     * @return MobileInterface
     */
    public function getSender();

    /**
     * @return MobileInterface
     */
    public function getReceiver();

    /**
     * @return string
     */
    public function getText();
}