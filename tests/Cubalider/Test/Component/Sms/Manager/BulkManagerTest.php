<?php

namespace Cubalider\Test\Component\Sms\Manager;

use Cubalider\Component\Sms\Manager\BulkManager;
use Cubalider\Component\Sms\Manager\Fit\FirstInQueueFit;
use Cubalider\Component\Sms\Manager\Fit\OrderQueueFit;
use Cubalider\Component\Sms\Model\Bulk;
use Yosmanyga\Component\Dql\Fit\AndFit;
use Yosmanyga\Component\Dql\Fit\Builder;
use Gedmo\Sortable\SortableListener;

/**
 * @author Yosmany Garcia <yosmanyga@gmail.com>
 * @author Manuel Emilio Carpio <mectwork@gmail.com>
 */
class BulkManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers \Cubalider\Component\Sms\Manager\BulkManager::__construct
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
        $manager = new BulkManager($em, $builder);

        $this->assertAttributeEquals($em, 'em', $manager);
        $this->assertAttributeEquals($builder, 'builder', $manager);
    }

    /**
     * @covers \Cubalider\Component\Sms\Manager\BulkManager::__construct
     */
    public function testConstructorWithDefaultParameters()
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->getMockBuilder('Doctrine\ORM\EntityManagerInterface')
            ->getMock();
        /** @var \Doctrine\ORM\EntityManager $em */
        $manager = new BulkManager($em);

        $this->assertAttributeEquals(new Builder($em), 'builder', $manager);
    }

    /**
     * @covers \Cubalider\Component\Sms\Manager\BulkManager::push
     */
    public function testPush()
    {
        $em = $this->getMock('Doctrine\ORM\EntityManagerInterface');
        $bulk = new Bulk();
        /** @var \Doctrine\ORM\EntityManager $em */
        $manager = new BulkManager($em);

        /** @var \PHPUnit_Framework_MockObject_MockObject $em */
        $em
            ->expects($this->once())->method('persist')
            ->with($bulk);
        $em
            ->expects($this->once())->method('flush');

        $this->assertEquals($bulk, $manager->push($bulk));
    }

    /**
     * @covers \Cubalider\Component\Sms\Manager\BulkManager::approach
     * @covers \Cubalider\Component\Sms\Manager\BulkManager::getFirst
     */
    public function testApproach()
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
            ->setMethods(array('getOneOrNullResult'))
            ->getMockForAbstractClass();
        /** @var \Doctrine\ORM\EntityManager $em */
        /** @var \Yosmanyga\Component\Dql\Fit\Builder $builder */
        $manager = new BulkManager($em, $builder);

        /** @var \PHPUnit_Framework_MockObject_MockObject $builder */
        $builder
            ->expects($this->once())
            ->method('build')
            ->with(
                'Cubalider\Component\Sms\Model\Bulk',
                new AndFit(
                    array(
                        new OrderQueueFit(),
                        new FirstInQueueFit()
                    ))
            )
            ->will($this->returnValue($qb));
        $qb
            ->expects($this->once())
            ->method('getQuery')
            ->will($this->returnValue($query));
        $query
            ->expects($this->once())
            ->method('getOneOrNullResult')
            ->will($this->returnValue($bulk = new Bulk()));

        $this->assertEquals($bulk, $manager->approach());
    }

    /**
     * @covers \Cubalider\Component\Sms\Manager\BulkManager::approach
     * @covers \Cubalider\Component\Sms\Manager\BulkManager::getFirst
     */
    public function testApproachWithNullBulk()
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
            ->setMethods(array('getOneOrNullResult'))
            ->getMockForAbstractClass();
        /** @var \Doctrine\ORM\EntityManager $em */
        /** @var \Yosmanyga\Component\Dql\Fit\Builder $builder */
        $manager = new BulkManager($em, $builder);

        /** @var \PHPUnit_Framework_MockObject_MockObject $builder */
        $builder
            ->expects($this->once())
            ->method('build')
            ->with(
                'Cubalider\Component\Sms\Model\Bulk',
                new AndFit(
                    array(
                        new OrderQueueFit(),
                        new FirstInQueueFit()
                    ))
            )
            ->will($this->returnValue($qb));
        $qb
            ->expects($this->once())
            ->method('getQuery')
            ->will($this->returnValue($query));
        $query
            ->expects($this->once())
            ->method('getOneOrNullResult')
            ->will($this->returnValue(null));


        $this->assertEquals(null, $manager->approach());
    }

    /**
     * @covers \Cubalider\Component\Sms\Manager\BulkManager::pop
     * @covers \Cubalider\Component\Sms\Manager\BulkManager::getFirst
     */
    public function tesPop()
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
            ->setMethods(array('getOneOrNullResult'))
            ->getMockForAbstractClass();
        /** @var \Doctrine\ORM\EntityManager $em */
        /** @var \Yosmanyga\Component\Dql\Fit\Builder $builder */
        $manager = new BulkManager($em, $builder);

        /** @var \PHPUnit_Framework_MockObject_MockObject $builder */
        $builder
            ->expects($this->once())
            ->method('build')
            ->with(
                'Cubalider\Component\Sms\Model\Bulk',
                new AndFit(
                    array(
                        new OrderQueueFit(),
                        new FirstInQueueFit()
                    ))
            )
            ->will($this->returnValue($qb));
        $qb
            ->expects($this->once())
            ->method('getQuery')
            ->will($this->returnValue($query));
        $query
            ->expects($this->once())
            ->method('getOneOrNullResult')
            ->will($this->returnValue($bulk = new Bulk()));

        /** @var \PHPUnit_Framework_MockObject_MockObject $em */
        $em
            ->expects($this->once())->method('remove')
            ->with($bulk);
        $em
            ->expects($this->once())->method('flush');

        $manager->pop();
    }
}