<?php

namespace Muchacuba\Component\Sms\Manager;

use Cubalider\Component\Sms\Manager\BulkManagerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Muchacuba\Component\Sms\Entity\BulkInterface;

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
     * @var string
     */
    private $class;

    /**
     * @var \Doctrine\ORM\EntityRepository
     */
    private $repository;

    /**
     * Constructor
     *
     * Additionally it creates a repository using $em, for given class
     *
     * @param EntityManagerInterface $em
     * @param string                 $class
     */
    public function __construct(
        EntityManagerInterface $em,
        $class = 'Muchacuba\Component\Sms\Entity\Bulk'
    )
    {
        $this->em = $em;
        $this->class = $em->getClassMetadata($class)->getName();
        $this->repository = $this->em->getRepository($class);
    }

    /**
     * Gets the bulk with first position
     *
     * @return BulkInterface
     */
    public function pop() 
    {
        $bulk = $this->repository
            ->createQueryBuilder('G')
            ->orderBy('G.position')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
        
        if (null !== $bulk) {
            $this->em->remove($bulk);
            $this->em->flush();
        }
        
        return $bulk;
    }
}