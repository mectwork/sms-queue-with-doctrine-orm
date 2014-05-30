<?php

namespace Cubalider\Component\Sms\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Cubalider\Component\Sms\Entity\BulkInterface;

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
     * @var string
     */
    private $class;

    /**
     * Constructor.
     * Additionally it creates a repository using $em, for given class
     *
     * @param EntityManagerInterface $em
     * @param string                 $class
     */
    public function __construct(
        EntityManagerInterface $em,
        $class = 'Cubalider\Component\Sms\Entity\Bulk'
    )
    {
        $this->em = $em;
        $this->repository = $this->em->getRepository($class);
        $this->class = $em->getClassMetadata($class)->getName();
    }

    /**
     * Adds a bulk into the last position.
     *
     * @internal
     * @throws \InvalidArgumentException if class doest' implements
     *                                   BulkInterface
     * @return BulkInterface
     */
    public function push()
    {
        $class = $this->class;
        $bulk = new $class;
        if (!$bulk instanceof BulkInterface) {
            throw new \InvalidArgumentException(sprintf('Class %s must implement Cubalider\Component\Sms\Entity\BulkInterface', $class));
        }

        $this->em->persist($bulk);
        $this->em->flush();

        return $bulk;
    }

    /**
     * Gets the bulk at first position.
     *
     * @internal
     * @return BulkInterface|null
     */
    public function approach()
    {
        return $this->getFirst();
    }

    /**
     * Removes the bulk at first position.
     *
     * @internal
     * @return void
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
     * @return BulkInterface|null
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