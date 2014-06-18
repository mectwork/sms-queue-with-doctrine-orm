<?php

namespace Cubalider\Test\Component\Sms\Manager;

use Cubalider\Component\Mobile\Model\Mobile;
use Cubalider\Component\Sms\Manager\BulkManager;
use Cubalider\Test\Component\Sms\EntityManagerBuilder;
use Cubalider\Component\Sms\Manager\MessageManager;
use Cubalider\Component\Sms\Model\Message;
use Cubalider\Component\Sms\Model\Bulk;
use Doctrine\ORM\EntityManager;
use Gedmo\Sortable\SortableListener;

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
                sprintf("%s/../../../../../../src/Cubalider/Component/Sms/Resources/config/doctrine", __DIR__),
                sprintf("%s/../../../../../../vendor/cubalider/mobile-with-doctrine-orm/src/Cubalider/Component/Mobile/Resources/config/doctrine", __DIR__)
            ),
            array(
                'Cubalider\Component\Sms\Model\Bulk',
                'Cubalider\Component\Sms\Model\Message',
                'Cubalider\Component\Mobile\Model\Mobile'
            ),
            array(
                new SortableListener()
            )
        );
    }

    /**
     * @covers \Cubalider\Component\Sms\Manager\MessageManager::__construct
     */
    public function testConstructorWithDefaultValues()
    {
        $manager = new MessageManager($this->em);

        $this->assertAttributeEquals($this->em, 'em', $manager);
        $this->assertAttributeEquals($this->em->getRepository('Cubalider\Component\Sms\Model\Message'), 'repository', $manager);
        $this->assertAttributeEquals(new BulkManager($this->em), 'bulkManager', $manager);
    }

    /**
     * @covers \Cubalider\Component\Sms\Manager\MessageManager::__construct
     */
    public function testConstructorWithValues()
    {
        $bulkManager = $this->getMock('Cubalider\Component\Sms\Manager\BulkManagerInterface');
        /** @var \Cubalider\Component\Sms\Manager\BulkManagerInterface $bulkManager */
        $manager = new MessageManager($this->em, $bulkManager);

        $this->assertAttributeEquals($bulkManager, 'bulkManager', $manager);
    }

    /**
     * @covers \Cubalider\Component\Sms\Manager\MessageManager::push
     */
    public function testPushWithEmptyArray()
    {
        $messageManager = new MessageManager($this->em);
        $messageRepository = $this->em->getRepository('Cubalider\Component\Sms\Model\Message');
        $bulkRepository = $this->em->getRepository('Cubalider\Component\Sms\Model\Bulk');

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

        $bulkRepository = $this->em->getRepository('Cubalider\Component\Sms\Model\Bulk');
        $bulks = $bulkRepository->findAll();

        $this->assertEquals(1, count($bulks));

        $messageRepository = $this->em->getRepository('Cubalider\Component\Sms\Model\Message');
        /** @var Message[] $messages */
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
     * @covers \Cubalider\Component\Sms\Manager\MessageManager::push
     * @expectedException \InvalidArgumentException
     */
    public function testPushWithInvalidClassInConstruct()
    {
        $metadata = $this->getMock('Doctrine\Common\Persistence\Mapping\ClassMetadata');
        $em = $this->getMock('Doctrine\ORM\EntityManagerInterface');

        $em->expects($this->once())->method('getRepository');
        $em->expects($this->once())->method('getClassMetadata')->will($this->returnValue($metadata));
        $metadata->expects($this->once())->method('getName')->will($this->returnValue('stdClass'));
         
        $manager = new MessageManager($em, 'stdClass', new BulkManager($this->em));
        $manager->push(array(new Message()));
    }

    /**
     * @covers \Cubalider\Component\Sms\Manager\MessageManager::push
     * @expectedException \InvalidArgumentException
     */
    public function testPushWithInvalidClassInMessagesArray()
    {
        $metadata = $this->getMock('Doctrine\Common\Persistence\Mapping\ClassMetadata');
        $em = $this->getMock('Doctrine\ORM\EntityManagerInterface');

        $em->expects($this->once())->method('getRepository');
        $em->expects($this->once())->method('getClassMetadata')->will($this->returnValue($metadata));
        $metadata->expects($this->once())->method('getName')->will($this->returnValue('stdClass'));

        $manager = new MessageManager($em, 'Cubalider\Component\Sms\Entity\Message', new BulkManager($this->em));
        $manager->push(array(new \stdClass()));
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
        $messageRepository = $this->em->getRepository('Cubalider\Component\Sms\Model\Message');
        $bulkRepository = $this->em->getRepository('Cubalider\Component\Sms\Model\Bulk');

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
        $messageManager->pop(1);
        $this->assertEquals(1, $messageManager->estimate($bulk));
        $messageManager->pop(1);
        $this->assertEquals(0, $messageManager->estimate($bulk));
        $messageManager->pop(1);
        $this->assertEquals(false, $messageManager->estimate($bulk));
    }
}