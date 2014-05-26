<?php

namespace Cubalider\Test\Component\Sms\Entity;

use Cubalider\Component\Sms\Entity\Message;

/**
 * @author Yusliel Garcia <yosmanyga@gmail.com>
 */
class MessageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers \Cubalider\Component\Sms\Entity\Message::getId
     */
    public function testId()
    {
        $message = new Message();
        $this->assertNull($message->getId());
    }

    /**
     * @covers \Cubalider\Component\Sms\Entity\Message::setReceiver
     * @covers \Cubalider\Component\Sms\Entity\Message::getReceiver
     */
    public function testReceiver()
    {
        $message = new Message();
        $this->assertNull($message->getReceiver());

        /** @var \Cubalider\Component\Mobile\Entity\MobileInterface $receiver*/
        $receiver = $this->getMock('Cubalider\Component\Mobile\Entity\MobileInterface');
        $message->setReceiver($receiver);
        $this->assertEquals($receiver, $message->getReceiver());
    }

    /**
     * @covers \Cubalider\Component\Sms\Entity\Message::setSender
     * @covers \Cubalider\Component\Sms\Entity\Message::getSender
     */
    public function testSender()
    {
        $message = new Message();
        $this->assertNull($message->getSender());

        /** @var \Cubalider\Component\Mobile\Entity\MobileInterface $sender */
        $sender = $this->getMock('Cubalider\Component\Mobile\Entity\MobileInterface');
        $message->setSender($sender);
        $this->assertEquals($sender, $message->getSender());
    }

    /**
     * @covers \Cubalider\Component\Sms\Entity\Message::setText
     * @covers \Cubalider\Component\Sms\Entity\Message::getText
     */
    public function testText()
    {
        $message = new Message();
        $this->assertNull($message->getText());

        $message->setText('foo');
        $this->assertEquals('foo', $message->getText());
    }

    /**
     * @covers \Cubalider\Component\Sms\Entity\Message::setBulk
     * @covers \Cubalider\Component\Sms\Entity\Message::getBulk
     */
    public function testBulk()
    {
        $message = new Message();
        $this->assertNull($message->getSender());

        /** @var \Cubalider\Component\Sms\Entity\BulkInterface $bulk */
        $bulk = $this->getMock('Cubalider\Component\Sms\Entity\BulkInterface');
        $message->setBulk($bulk);
        $this->assertEquals($bulk, $message->getBulk());
    }
}