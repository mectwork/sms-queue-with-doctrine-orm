<?php

namespace Muchacuba\Test\Component\Sms\Manager;

use Muchacuba\Component\Sms\Manager\BulkManager;
use Muchacuba\Test\Component\Sms\EntityManagerBuilder;
use Muchacuba\Component\Sms\Entity\Bulk;
use Doctrine\ORM\EntityManager;
use Gedmo\Sortable\SortableListener;

/**
 * @author Yosmany Garcia <yosmanyga@gmail.com>
 */
class BulkManagerTest extends \PHPUnit_Framework_TestCase
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
                'Muchacuba\Component\Sms\Entity\Bulk',
                'Muchacuba\Component\Sms\Entity\Message',
                'Muchacuba\Component\Mobile\Entity\Mobile'
            ),
            array(
                new SortableListener()
            ),
            array(
                'Muchacuba\Component\Sms\Entity\BulkInterface' => 'Muchacuba\Component\Sms\Entity\Bulk',
                'Muchacuba\Component\Sms\Entity\MessageInterface' => 'Muchacuba\Component\Sms\Entity\Message',
                'Muchacuba\Component\Mobile\Entity\MobileInterface' => 'Muchacuba\Component\Mobile\Entity\Mobile',
            )
        );
    }

    /**
     * @covers \Muchacuba\Component\Sms\Manager\BulkManager::__construct
     */
    public function testConstructor()
    {
        $class = 'Muchacuba\Component\Sms\Entity\Bulk';
        $metadata = $this->getMock('Doctrine\Common\Persistence\Mapping\ClassMetadata');
        $metadata->expects($this->once())->method('getName')->will($this->returnValue($class));
        $em = $this->getMock('Doctrine\ORM\EntityManagerInterface');
        $em->expects($this->once())->method('getClassMetadata')->with($class)->will($this->returnValue($metadata));
        /** @var \Doctrine\ORM\EntityManagerInterface $em */
        $manager = new BulkManager($em, $class);

        $this->assertAttributeEquals($em, 'em', $manager);
        $this->assertAttributeEquals($class, 'class', $manager);
        $this->assertAttributeEquals($em->getRepository('Muchacuba\Component\Sms\Entity\Bulk'), 'repository', $manager);
    }
    
    /**
     * @covers \Muchacuba\Component\Sms\Manager\BulkManager::pop
     */
    public function testPop()
    {
        /* Fixtures */

        $bulk1 = new Bulk();
        $this->em->persist($bulk1);
        $bulk2 = new Bulk();
        $this->em->persist($bulk2);
        $this->em->flush();

        /* Tests */

        $manager = new BulkManager($this->em);
        $this->assertEquals($bulk1, $manager->pop());

        $repository = $this->em->getRepository('Muchacuba\Component\Sms\Entity\Bulk');
        $this->assertEquals(1, count($repository->findAll()));
    }
}