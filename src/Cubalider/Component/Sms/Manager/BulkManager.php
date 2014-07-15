<?php

namespace Cubalider\Component\Sms\Manager;

use Cubalider\Component\Sms\Manager\Fit\FirstInQueueFit;
use Cubalider\Component\Sms\Manager\Fit\OrderQueueFit;
use Doctrine\ORM\EntityManagerInterface;
use Cubalider\Component\Sms\Model\Bulk;
use Yosmanyga\Component\Dql\Fit\AndFit;
use Yosmanyga\Component\Dql\Fit\Builder;

/**
 * @author Yosmany Garcia <yosmanyga@gmail.com>
 */
class BulkManager implements BulkManagerInterface
{
    /**
     * @var string
     */
    private $class = 'Cubalider\Component\Sms\Model\Bulk';

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $em;

    /**
     * @var Builder;
     */
    private $builder;

    /**
     * Constructor.
     *
     * @param EntityManagerInterface $em
     * @param Builder $builder
     */
    public function __construct(EntityManagerInterface $em, Builder $builder = null)
    {
        $this->em = $em;
        $this->builder = $builder ? : new Builder($em);
    }

    /**
     * @inheritdoc
     */
    public function push()
    {
        $bulk = new Bulk();
        $this->em->persist($bulk);
        $this->em->flush();

        return $bulk;
    }

    /**
     * @inheritdoc
     */
    public function approach()
    {
        return $this->getFirst();
    }

    /**
     * @inheritdoc
     */
    public function pop()
    {
        $bulk = $this->getFirst();

        if ($bulk) {
            $this->em->remove($bulk);
            $this->em->flush();
        }
    }

    /**
     * Returns the bulk at first position.
     *
     * @return Bulk|null
     */
    /**
     * Returns the bulk at first position.
     *
     * @return Bulk|null
     */
    private function getFirst()
    {
        $qb = $this->builder->build(
            $this->class,
            new AndFit(array(
                new OrderQueueFit(),
                new FirstInQueueFit()
            ))
        );

        return $qb
            ->getQuery()
            ->getOneOrNullResult();
    }
}
