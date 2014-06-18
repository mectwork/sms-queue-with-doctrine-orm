<?php

namespace Cubalider\Component\Sms\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Cubalider\Component\Sms\Model\Bulk;

/**
 * @author Yosmany Garcia <yosmanyga@gmail.com>
 */
class BulkManager implements BulkManagerInterface
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $em;

    /**
     * @var \Doctrine\ORM\EntityRepository
     */
    private $repository;

    /**
     * Constructor.
     * Additionally it creates a repository using $em, for given class
     *
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->repository = $this->em->getRepository('Cubalider\Component\Sms\Model\Bulk');
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
    private function getFirst()
    {
        return $this->repository
            ->createQueryBuilder('G')
            ->orderBy('G.position')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}