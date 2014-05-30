<?php

namespace Cubalider\Test\Component\Sms\Manager;

use Cubalider\Component\Mobile\Entity\Mobile;
use Cubalider\Component\Sms\Entity\MessageInterface;
use Cubalider\Test\Component\Sms\EntityManagerBuilder;
use Cubalider\Component\Sms\Manager\MessageManager;
use Cubalider\Component\Sms\Manager\BulkManager;
use Cubalider\Component\Sms\Entity\Message;
use Cubalider\Component\Sms\Entity\Bulk;
use Doctrine\ORM\EntityManager;

/**
 * @author Yosmany Garcia <yosmanyga@gmail.com>
 * @author Yusliel Garcia <yuslielg@gmail.com>
 */
class MessageManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EntityManager
     */
    protected $em;
    
    public function setUp()
    {
        $builder = new EntityManagerBuilder();
        $this->em = $builder->createEntityManager(
            array(
                'Cubalider\Component\Sms\Entity\Bulk',
                'Cubalider\Component\Sms\Entity\Message',
                'Cubalider\Component\Mobile\Entity\Mobile'
            ),
            array(
                
            ),
            array(
                'Cubalider\Component\Sms\Entity\BulkInterface' => 'Cubalider\Component\Sms\Entity\Bulk',
                'Cubalider\Component\Sms\Entity\MessageInterface' => 'Cubalider\Component\Sms\Entity\Message',
                'Cubalider\Component\Mobile\Entity\MobileInterface' => 'Cubalider\Component\Mobile\Entity\Mobile'
            )
        );
    }

    /**
     * @covers \Cubalider\Component\Sms\Manager\MessageManager::__construct
     */
    public function testConstructorWithDefaultValues()
    {
        $messageClass = 'Cubalider\Component\Sms\Entity\Message';
        $messageMetadata = $this->getMock('Doctrine\Common\Persistence\Mapping\ClassMetadata');
        $bulkClass = 'Cubalider\Component\Sms\Entity\Bulk';
        $bulkMetadata = $this->getMock('Doctrine\Common\Persistence\Mapping\ClassMetadata');
        $em = $this->getMock('Doctrine\ORM\EntityManagerInterface');

        $em->expects($this->at(0))->method('getRepository')->with($messageClass)->will($this->returnValue('r'));
        $em->expects($this->at(1))->method('getClassMetadata')->with($messageClass)->will($this->returnValue($messageMetadata));
        $messageMetadata->expects($this->once())->method('getName')->will($this->returnValue('c'));
        $em->expects($this->at(2))->method('getRepository')->with($bulkClass);
        $em->expects($this->at(3))->method('getClassMetadata')->with($bulkClass)->will($this->returnValue($bulkMetadata));
        $bulkMetadata->expects($this->once())->method('getName');

        /** @var \Doctrine\ORM\EntityManagerInterface $em */
        $manager = new MessageManager($em);

        $this->assertAttributeEquals($em, 'em', $manager);
        $this->assertAttributeEquals('r', 'repository', $manager);
        $this->assertAttributeEquals('c', 'class', $manager);
    }

    /**
     * @covers \Cubalider\Component\Sms\Manager\MessageManager::__construct
     */
    public function testConstructorWithValues()
    {
        $messageClass = 'Foo';
        $messageMetadata = $this->getMock('Doctrine\Common\Persistence\Mapping\ClassMetadata');
        $em = $this->getMock('Doctrine\ORM\EntityManagerInterface');
        $bulkManager = $this->getMock('Cubalider\Component\Sms\Manager\BulkManagerInterface');

        $em->expects($this->once())->method('getRepository')->with($messageClass);
        $em->expects($this->once())->method('getClassMetadata')->with($messageClass)->will($this->returnValue($messageMetadata));
        $messageMetadata->expects($this->once())->method('getName');

        /** @var \Doctrine\ORM\EntityManagerInterface $em */
        /** @var \Cubalider\Component\Sms\Manager\BulkManagerInterface $bulkManager */
        $manager = new MessageManager($em, $messageClass, $bulkManager);

        $this->assertAttributeEquals($bulkManager, 'bulkManager', $manager);
    }

    /**
     * @covers \Cubalider\Component\Sms\Manager\MessageManager::push
     */
    public function testPushWithEmptyArray()
    {
        $messageManager = new MessageManager($this->em);
        $messageRepository = $this->em->getRepository('Cubalider\Component\Sms\Entity\Message');
        $bulkRepository = $this->em->getRepository('Cubalider\Component\Sms\Entity\Bulk');

        $this->assertFalse($messageManager->push(array()));
        $this->assertEquals(0, count($messageRepository->findAll()));
        $this->assertEquals(0, count($bulkRepository->findAll()));
    }

    /**
     * @covers \Cubalider\Component\Sms\Manager\MessageManager::push
     */
    public function testPush()
    {
        /* Fixtures */

        $mobile = new Mobile();
        $mobile->setNumber('123');
        $this->em->persist($mobile);
        $this->em->flush();

        $message1 = new Message();
        $message1->setSender($mobile);
        $message1->setReceiver($mobile);
        $message1->setText('Message 1');

        $message2 = new Message();
        $message2->setSender($mobile);
        $message2->setReceiver($mobile);
        $message2->setText('Message 2');

        /* Tests */

        $messageManager = new MessageManager($this->em);
        $messageManager->push(array($message1, $message2));

        $bulkRepository = $this->em->getRepository('Cubalider\Component\Sms\Entity\Bulk');
        $bulks = $bulkRepository->findAll();
        $this->assertEquals(1, count($bulks));

        $messageRepository = $this->em->getRepository('Cubalider\Component\Sms\Entity\Message');
        /** @var MessageInterface[] $messages */
        $messages = $messageRepository->findAll();
        $this->assertEquals(2, count($messages));

        $this->assertEquals($messages[0]->getBulk(), $bulks[0]);
    }

    /**
     * @covers \Cubalider\Component\Sms\Manager\MessageManager::push
     * @expectedException \Exception
     */
    public function testPushWithInvalidMessage()
    {
        /* Fixtures */

        $mobile = new Mobile();
        $mobile->setNumber('123');
        $this->em->persist($mobile);
        $this->em->flush();

        $message1 = new Message();
        $message1->setSender($mobile);
        $message1->setReceiver($mobile);
        $message1->setText('Message 1');

        $message2 = new Message();

        /* Tests */

        $messageManager = new MessageManager($this->em);
        $messageManager->push(array($message1, $message2));
    }

    /**
     * @covers \Cubalider\Component\Sms\Manager\MessageManager::pop
     * @covers \Cubalider\Component\Sms\Manager\MessageManager::findMessages
     * @covers \Cubalider\Component\Sms\Manager\MessageManager::removeMessages
     */
    public function testPop()
    {
        /* Fixtures */

        $bulk1 = new Bulk();
        $this->em->persist($bulk1);

        $bulk2 = new Bulk();
        $this->em->persist($bulk2);

        $message1 = new Message();
        $message1->setBulk($bulk1);
        $message1->setText('Message 1');
        $this->em->persist($message1);

        $message2 = new Message();
        $message2->setBulk($bulk1);
        $message2->setText('Message 2');
        $this->em->persist($message2);

        $message3 = new Message();
        $message3->setBulk($bulk2);
        $message3->setText('Message 3');
        $this->em->persist($message3);

        $message4 = new Message();
        $message4->setBulk($bulk1);
        $message4->setText('Message 4');
        $this->em->persist($message4);

        $this->em->flush();

        /* Tests */

        $messageManager = new MessageManager($this->em);
        $messageRepository = $this->em->getRepository('Cubalider\Component\Sms\Entity\Message');
        $bulkRepository = $this->em->getRepository('Cubalider\Component\Sms\Entity\Bulk');

        $this->assertEquals(array($message1, $message2), $messageManager->pop(2));
        $this->assertEquals(2, count($messageRepository->findAll()));
        $this->assertEquals(array($message4), $messageManager->pop(2));
        $this->assertEquals(1, count($messageRepository->findAll()));
        $this->assertEquals(array(), $messageManager->pop(2));
        $this->assertEquals(1, count($bulkRepository->findAll()));
    }

    /**
     * @covers \Cubalider\Component\Sms\Manager\MessageManager::estimate
     */
    public function testEstimate()
    {
        /* Fixtures */

        $bulk = new Bulk();
        $this->em->persist($bulk);

        $message1 = new Message();
        $message1->setBulk($bulk);
        $message1->setText('Message 1');
        $this->em->persist($message1);

        $message2 = new Message();
        $message2->setBulk($bulk);
        $message2->setText('Message 2');
        $this->em->persist($message2);

        $this->em->flush();

        /* Tests */

        $messageManager = new MessageManager($this->em);
        $this->assertEquals(2, $messageManager->estimate($bulk));
    }

}