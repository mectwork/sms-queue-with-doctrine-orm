<?php

namespace Cubalider\Test\Component\Sms\Manager;


use Cubalider\Component\Sms\Manager\BulkManager;
use Cubalider\Component\Sms\Manager\MessageManager;
use Cubalider\Component\Sms\Model\Bulk;
use Cubalider\Component\Sms\Model\Message;
use Yosmanyga\Component\Dql\Fit\AndFit;
use Yosmanyga\Component\Dql\Fit\Builder;
use Yosmanyga\Component\Dql\Fit\LimitFit;
use Yosmanyga\Component\Dql\Fit\SelectCountFit;
use Yosmanyga\Component\Dql\Fit\WhereCriteriaFit;


/**
 * @author Yosmany Garcia <yosmanyga@gmail.com>
 * @author Yusliel Garcia <yuslielg@gmail.com>
 * @author Manuel Emilio Carpio <mectwork@gmail.com>
 */
class MessageManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers \Cubalider\Component\Sms\Manager\MessageManager::__construct
     */
    public function testConstructor()
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->getMockBuilder('Doctrine\ORM\EntityManagerInterface')
            ->getMock();
        /** @var \Yosmanyga\Component\Dql\Fit\Builder $builder */
        $builder = $this->getMockBuilder('Yosmanyga\Component\Dql\Fit\Builder')
            ->setConstructorArgs(array($em))
            ->getMock();
        $bulkManager = $this->getMockBuilder('Cubalider\Component\Sms\Manager\BulkManagerInterface')
            ->getMock();
        /** @var \Doctrine\ORM\EntityManager $em */
        /** @var \Yosmanyga\Component\Dql\Fit\Builder $builder */
        /** @var \Cubalider\Component\Sms\Manager\BulkManagerInterface $bulkManager */
        $manager = new MessageManager($em, $builder, $bulkManager);

        $this->assertAttributeEquals($em, 'em', $manager);
        $this->assertAttributeEquals($builder, 'builder', $manager);
        $this->assertAttributeEquals($bulkManager, 'bulkManager', $manager);
    }

    /**
     * @covers \Cubalider\Component\Sms\Manager\MessageManager::__construct
     */
    public function testConstructorWithDefaultParameters()
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->getMockBuilder('Doctrine\ORM\EntityManagerInterface')
            ->getMock();
        /** @var \Doctrine\ORM\EntityManager $em */
        $manager = new MessageManager($em);

        $this->assertAttributeEquals(new Builder($em), 'builder', $manager);
        $this->assertAttributeEquals(new BulkManager($em), 'bulkManager', $manager);
    }

    /**
     * @covers \Cubalider\Component\Sms\Manager\MessageManager::push
     */
    public function testPushWithEmptyArray()
    {
        $em = $this->getMock('Doctrine\ORM\EntityManagerInterface');
        /** @var \Doctrine\ORM\EntityManager $em */
        $manager = new MessageManager($em);

        $this->assertFalse($manager->push(array()));
    }

    /**
     * @covers \Cubalider\Component\Sms\Manager\MessageManager::push
     */
    public function testPush()
    {
        $em = $this->getMock('Doctrine\ORM\EntityManagerInterface');
        $builder = $this->getMockBuilder('Yosmanyga\Component\Dql\Fit\Builder')
            ->disableOriginalConstructor()
            ->getMock();
        $bulkManager = $this->getMockBuilder('\Cubalider\Component\Sms\Manager\BulkManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \Doctrine\ORM\EntityManager $em */
        /** @var \Yosmanyga\Component\Dql\Fit\Builder $builder */
        $manager = new MessageManager($em, $builder);
        $messages = array($message1 = new Message(), $message2 = new Message());
        $bulk = new Bulk();

        /** @var \PHPUnit_Framework_MockObject_MockObject $bulkManager */
        $bulkManager
            ->expects($this->any())
            ->method('push')
            ->will($this->returnValue($bulk));
        /** @var \PHPUnit_Framework_MockObject_MockObject $em */
        $em
            ->expects($this->once())->method('beginTransaction');

        $this->assertEquals($bulk, $manager->push($messages));
        $this->assertEquals($bulk, $message1->getBulk());
        $this->assertEquals($bulk, $message2->getBulk());
    }

    /**
     * @covers \Cubalider\Component\Sms\Manager\MessageManager::push
     * @expectedException \Exception
     */
    public function testPushWithInvalidMessage()
    {
        $em = $this->getMock('Doctrine\ORM\EntityManagerInterface');
        $builder = $this->getMockBuilder('Yosmanyga\Component\Dql\Fit\Builder')
            ->disableOriginalConstructor()
            ->getMock();
        $bulkManager = $this->getMockBuilder('\Cubalider\Component\Sms\Manager\BulkManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \Doctrine\ORM\EntityManager $em */
        /** @var \Yosmanyga\Component\Dql\Fit\Builder $builder */
        $manager = new MessageManager($em, $builder);
        $messages = "";
        $bulk = new Bulk();

        /** @var \PHPUnit_Framework_MockObject_MockObject $bulkManager */
        $bulkManager
            ->expects($this->any())
            ->method('push')
            ->will($this->returnValue($bulk));

        /** @var \PHPUnit_Framework_MockObject_MockObject $em */
        $em
            ->expects($this->once())->method('beginTransaction');
        $em
            ->expects($this->once())->method('rollback');

        $manager->push($messages);
    }

    /**
     * @covers \Cubalider\Component\Sms\Manager\MessageManager::pop
     */
    public function testPopWithMessages()
    {
        $em = $this->getMock('Doctrine\ORM\EntityManagerInterface');
        $builder = $this->getMockBuilder('Yosmanyga\Component\Dql\Fit\Builder')
            ->disableOriginalConstructor()
            ->getMock();
        $qb = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $query = $this->getMockBuilder('Doctrine\ORM\AbstractQuery')
            ->disableOriginalConstructor()
            ->setMethods(array('getResult'))
            ->getMockForAbstractClass();
        $bulkManager = $this->getMockBuilder('Cubalider\Component\Sms\Manager\BulkManager')
            ->setConstructorArgs(array($em))
            ->getMock();
        /** @var \Doctrine\ORM\EntityManager $em */
        /** @var \Yosmanyga\Component\Dql\Fit\Builder $builder */
        /** @var \Cubalider\Component\Sms\Manager\BulkManager $bulkManager */
        $manager = new MessageManager($em, $builder, $bulkManager);
        $bulk = new Bulk();
        $amount = 2;
        $messages = array($message1 = new Message(), $message2 = new Message());

        /** @var \PHPUnit_Framework_MockObject_MockObject $bulkManager */
        $bulkManager
            ->expects($this->once())
            ->method('approach')
            ->will($this->returnValue($bulk));

        /** @var \PHPUnit_Framework_MockObject_MockObject $builder */
        $builder
            ->expects($this->once())
            ->method('build')
            ->with(
                'Cubalider\Component\Sms\Model\Message',
                new AndFit(array(
                    new WhereCriteriaFit(array('bulk' => $bulk->getId())),
                    new LimitFit($amount)
                ))
            )
            ->will($this->returnValue($qb));
        $qb
            ->expects($this->once())
            ->method('getQuery')
            ->will($this->returnValue($query));
        $query
            ->expects($this->once())
            ->method('getResult')
            ->will($this->returnValue($messages));

        /** @var \PHPUnit_Framework_MockObject_MockObject $em */
        $em
            ->expects($this->at(1))->method('remove')
            ->with($message1);
        $em
            ->expects($this->at(1))->method('remove')
            ->with($message2);
        $em
            ->expects($this->once())->method('flush');

        $this->assertEquals($messages, $manager->pop($amount));
    }

    /**
     * @covers \Cubalider\Component\Sms\Manager\MessageManager::pop
     */
    public function testPopWithoutMessages()
    {
        $em = $this->getMock('Doctrine\ORM\EntityManagerInterface');
        $builder = $this->getMockBuilder('Yosmanyga\Component\Dql\Fit\Builder')
            ->disableOriginalConstructor()
            ->getMock();
        $qb = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $query = $this->getMockBuilder('Doctrine\ORM\AbstractQuery')
            ->disableOriginalConstructor()
            ->setMethods(array('getResult'))
            ->getMockForAbstractClass();
        $bulkManager = $this->getMockBuilder('Cubalider\Component\Sms\Manager\BulkManager')
            ->setConstructorArgs(array($em))
            ->getMock();
        /** @var \Doctrine\ORM\EntityManager $em */
        /** @var \Yosmanyga\Component\Dql\Fit\Builder $builder */
        /** @var \Cubalider\Component\Sms\Manager\BulkManager $bulkManager */
        $manager = new MessageManager($em, $builder, $bulkManager);
        $bulk = new Bulk();
        $amount = 2;
        $messages = array();

        /** @var \PHPUnit_Framework_MockObject_MockObject $bulkManager */
        $bulkManager
            ->expects($this->once())
            ->method('approach')
            ->will($this->returnValue($bulk));

        /** @var \PHPUnit_Framework_MockObject_MockObject $builder */
        $builder
            ->expects($this->once())
            ->method('build')
            ->with(
                'Cubalider\Component\Sms\Model\Message',
                new AndFit(array(
                    new WhereCriteriaFit(array('bulk' => $bulk->getId())),
                    new LimitFit($amount)
                ))
            )
            ->will($this->returnValue($qb));
        $qb
            ->expects($this->once())
            ->method('getQuery')
            ->will($this->returnValue($query));
        $query
            ->expects($this->once())
            ->method('getResult')
            ->will($this->returnValue($messages));


        /** @var \PHPUnit_Framework_MockObject_MockObject $bulkManager */
        $bulkManager
            ->expects($this->once())
            ->method('pop')
            ->will($this->returnValue('foobar'));

        /** @var \PHPUnit_Framework_MockObject_MockObject $em */
        $em
            ->expects($this->once())->method('flush');

        $this->assertEquals($messages, $manager->pop($amount));
    }

    /**
     * @covers \Cubalider\Component\Sms\Manager\MessageManager::estimate
     */
    public function testEstimate()
    {
        $em = $this->getMock('Doctrine\ORM\EntityManagerInterface');
        $builder = $this->getMockBuilder('Yosmanyga\Component\Dql\Fit\Builder')
            ->disableOriginalConstructor()
            ->getMock();
        $qb = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $query = $this->getMockBuilder('Doctrine\ORM\AbstractQuery')
            ->disableOriginalConstructor()
            ->setMethods(array('getSingleScalarResult'))
            ->getMockForAbstractClass();
        /** @var \Doctrine\ORM\EntityManager $em */
        /** @var \Yosmanyga\Component\Dql\Fit\Builder $builder */
        $manager = new MessageManager($em, $builder);
        $bulk = new Bulk();
        $bulk->setId(1);
        $messages = 4;

        /** @var \PHPUnit_Framework_MockObject_MockObject $builder */
        $builder
            ->expects($this->once())
            ->method('build')
            ->with(
                'Cubalider\Component\Sms\Model\Message',
                new AndFit(
                    array(
                        new SelectCountFit('id'),
                        new WhereCriteriaFit(array('bulk' => $bulk->getId()))
                    ))
            )
            ->will($this->returnValue($qb));
        $qb
            ->expects($this->once())
            ->method('getQuery')
            ->will($this->returnValue($query));
        $query
            ->expects($this->once())
            ->method('getSingleScalarResult')
            ->will($this->returnValue($messages));

        $this->assertEquals($messages, $manager->estimate($bulk));
    }

    /**
     * @covers \Cubalider\Component\Sms\Manager\MessageManager::estimate
     * @expectedException \InvalidArgumentException
     */
    public function testEstimateCatchingException()
    {
        $em = $this->getMock('Doctrine\ORM\EntityManagerInterface');
        $builder = $this->getMockBuilder('Yosmanyga\Component\Dql\Fit\Builder')
            ->disableOriginalConstructor()
            ->getMock();
        $qb = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $query = $this->getMockBuilder('Doctrine\ORM\AbstractQuery')
            ->disableOriginalConstructor()
            ->setMethods(array('getSingleScalarResult'))
            ->getMockForAbstractClass();
        /** @var \Doctrine\ORM\EntityManager $em */
        /** @var \Yosmanyga\Component\Dql\Fit\Builder $builder */
        $manager = new MessageManager($em, $builder);
        $bulk = new Bulk;

        /** @var \PHPUnit_Framework_MockObject_MockObject $builder */
        $builder
            ->expects($this->once())
            ->method('build')
            ->with(
                'Cubalider\Component\Sms\Model\Message',
                new AndFit(
                    array(
                        new SelectCountFit('id'),
                        new WhereCriteriaFit(array('bulk' => $bulk->getId()))
                    ))
            )
            ->will($this->returnValue($qb));
        $qb
            ->expects($this->once())
            ->method('getQuery')
            ->will($this->returnValue($query));
        $query
            ->expects($this->once())
            ->method('getSingleScalarResult')
            ->will($this->throwException(new \InvalidArgumentException()));

        $this->assertFalse($manager->estimate($bulk));
    }
}