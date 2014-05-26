<?php

namespace Cubalider\Test\Component\Sms\Manager;

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
                'Cubalider\Component\Sms\Entity\Message'
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
    public function testConstructor()
    {
        $class = 'Cubalider\Component\Sms\Entity\Message';
        $metadata = $this->getMock('Doctrine\Common\Persistence\Mapping\ClassMetadata');
        $metadata->expects($this->once())->method('getName')->will($this->returnValue($class));
        $em = $this->getMock('Doctrine\ORM\EntityManagerInterface');
        $em->expects($this->once())->method('getClassMetadata')->with($class)->will($this->returnValue($metadata));
        /** @var \Doctrine\ORM\EntityManagerInterface $em */
        $manager = new MessageManager($em, $class);

        $this->assertAttributeEquals($em, 'em', $manager);
        $this->assertAttributeEquals($class, 'class', $manager);
        $this->assertAttributeEquals($em->getRepository('Cubalider\Component\Sms\Entity\Message'), 'repository', $manager);
    }
    
    /**
     * @covers \Cubalider\Component\Sms\Manager\MessageManager::push
     */
    public function testPush()
    {
        /* Fixtures */

        $message1 = new Message();
        $message1->setText('Message 1');
        
        $message2 = new Message();
        $message2->setText('Message 1');
        
        /* Tests */
        
        $messageManager = new MessageManager($this->em);
        $messageManager->push(array($message1, $message2));
        
        $bulkRepository = $this->em->getRepository('Cubalider\Component\Sms\Entity\Bulk');
        $bulks = $bulkRepository->findAll();
        $this->assertEquals(1, count($bulks));
        
        $messageRepository = $this->em->getRepository('Cubalider\Component\Sms\Entity\Message');
        $this->assertEquals(2, count($messageRepository->findAll()));        
        
        $this->assertEquals($message1->getBulk(), $bulks[0]);
    }

    /**
     * @covers \Cubalider\Component\Sms\Manager\MessageManager::push
     */
    public function testPushEmptyArray()
    {
        $messageManager = new MessageManager($this->em);
        $messageRepository = $this->em->getRepository('Cubalider\Component\Sms\Entity\Message');

        $messageManager->push(array());
        $this->assertEquals(0, count($messageRepository->findAll()));
    }
    
    /**
     * @covers \Cubalider\Component\Sms\Manager\MessageManager::pop
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

        $this->assertEquals(array($message1, $message2, $message4), $messageManager->pop($bulk1));

        $messageRepository = $this->em->getRepository('Cubalider\Component\Sms\Entity\Message');
        $this->assertEquals(1, count($messageRepository->findAll()));
    }

    /**
     * @covers \Cubalider\Component\Sms\Manager\MessageManager::pop
     */
    public function testPopWithAmount()
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
        $message3->setBulk($bulk1);
        $message3->setText('Message 3');
        $this->em->persist($message3);

        $message4 = new Message();
        $message4->setBulk($bulk2);
        $message4->setText('Message 4');
        $this->em->persist($message4);

        $this->em->flush();

        /* Tests */

        $messageManager = new MessageManager($this->em);

        $this->assertEquals(array($message1, $message2), $messageManager->pop($bulk1, 2));
    }

    /**
     * @covers \Cubalider\Component\Sms\Manager\MessageManager::pop
     */
    public function testPopWithNoMoreMessages()
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

        $this->em->flush();

        /* Tests */

        $messageManager = new MessageManager($this->em);
        $this->assertFalse($messageManager->pop($bulk2));

        $bulkRepository = $this->em->getRepository('Cubalider\Component\Sms\Entity\Bulk');
        $this->assertEquals(1, count($bulkRepository->findAll()));
    }
}