<?php

namespace Cubalider\Test\Component\Sms\Manager;

use Cubalider\Component\Sms\Manager\BulkManager;
use Cubalider\Test\Component\Sms\EntityManagerBuilder;
use Cubalider\Component\Sms\Model\Bulk;
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
                sprintf("%s/../../../../../../src/Cubalider/Component/Sms/Resources/config/doctrine", __DIR__)
            ),
            array(
                'Cubalider\Component\Sms\Model\Bulk'
            ),
            array(
                new SortableListener()
            )
        );
    }

    /**
     * @covers \Cubalider\Component\Sms\Manager\BulkManager::__construct
     */
    public function testConstructor()
    {
        $manager = new BulkManager($this->em);

        $this->assertAttributeEquals($this->em, 'em', $manager);
        $this->assertAttributeEquals($this->em->getRepository('Cubalider\Component\Sms\Model\Bulk'), 'repository', $manager);
    }

    /**
     * @covers \Cubalider\Component\Sms\Manager\BulkManager::push
     */
    public function testPush()
    {
        /* Tests */

        $bulk1 = new Bulk();
        $manager = new BulkManager($this->em);
        $manager->push($bulk1);

        $repository = $this->em->getRepository('Cubalider\Component\Sms\Model\Bulk');
        $this->assertEquals(1, count($repository->findAll()));
    }

    /**
     * @covers \Cubalider\Component\Sms\Manager\BulkManager::approach
     * @covers \Cubalider\Component\Sms\Manager\BulkManager::getFirst
     */
    public function testApproach()
    {
        /* Fixtures */

        $bulk1 = new Bulk();
        $this->em->persist($bulk1);
        $bulk2 = new Bulk();
        $this->em->persist($bulk2);
        $this->em->flush();

        /* Tests */

        $manager = new BulkManager($this->em);
        $this->assertEquals($bulk1, $manager->approach());
    }

    /**
     * @covers \Cubalider\Component\Sms\Manager\BulkManager::pop
     * @covers \Cubalider\Component\Sms\Manager\BulkManager::getFirst
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
        $manager->pop();

        $repository = $this->em->getRepository('Cubalider\Component\Sms\Model\Bulk');
        $this->assertEquals(1, count($repository->findAll()));
    }
}