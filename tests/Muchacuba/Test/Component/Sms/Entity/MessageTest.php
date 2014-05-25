<?php

namespace Muchacuba\Test\Component\Sms\Entity;

use Muchacuba\Component\Sms\Entity\Message;

/**
 * @author Yusliel Garcia <yosmanyga@gmail.com>
 */
class MessageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers \Muchacuba\Component\Sms\Entity\Message::getId
     */
    public function testId()
    {
        $message = new Message();
        $this->assertNull($message->getId());
    }

    /**
     * @covers \Muchacuba\Component\Sms\Entity\Message::setReceiver
     * @covers \Muchacuba\Component\Sms\Entity\Message::getReceiver
     */
    public function testReceiver()
    {
        $message = new Message();
        $this->assertNull($message->getReceiver());

        /** @var \Muchacuba\Component\Mobile\Entity\MobileInterface $receiver*/
        $receiver = $this->getMock('Muchacuba\Component\Mobile\Entity\MobileInterface');
        $message->setReceiver($receiver);
        $this->assertEquals($receiver, $message->getReceiver());
    }

    /**
     * @covers \Muchacuba\Component\Sms\Entity\Message::setSender
     * @covers \Muchacuba\Component\Sms\Entity\Message::getSender
     */
    public function testSender()
    {
        $message = new Message();
        $this->assertNull($message->getSender());

        /** @var \Muchacuba\Component\Mobile\Entity\MobileInterface $sender */
        $sender = $this->getMock('Muchacuba\Component\Mobile\Entity\MobileInterface');
        $message->setSender($sender);
        $this->assertEquals($sender, $message->getSender());
    }

    /**
     * @covers \Muchacuba\Component\Sms\Entity\Message::setText
     * @covers \Muchacuba\Component\Sms\Entity\Message::getText
     */
    public function testText()
    {
        $message = new Message();
        $this->assertNull($message->getText());

        $message->setText('foo');
        $this->assertEquals('foo', $message->getText());
    }

    /**
     * @covers \Muchacuba\Component\Sms\Entity\Message::setBulk
     * @covers \Muchacuba\Component\Sms\Entity\Message::getBulk
     */
    public function testBulk()
    {
        $message = new Message();
        $this->assertNull($message->getSender());

        /** @var \Muchacuba\Component\Sms\Entity\BulkInterface $bulk */
        $bulk = $this->getMock('Muchacuba\Component\Sms\Entity\BulkInterface');
        $message->setBulk($bulk);
        $this->assertEquals($bulk, $message->getBulk());
    }
}