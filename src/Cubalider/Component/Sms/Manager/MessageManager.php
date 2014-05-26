<?php

namespace Cubalider\Component\Sms\Manager;

use Cubalider\Component\Sms\Manager\MessageManagerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Cubalider\Component\Sms\Entity\Bulk;
use Cubalider\Component\Sms\Entity\BulkInterface;
use Cubalider\Component\Sms\Entity\MessageInterface;

/**
 * @author Yosmany Garcia <yosmanyga@gmail.com>
 * @author Yusliel Garcia <yuslielg@gmail.com>
 */
class MessageManager implements MessageManagerInterface
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
        $class = 'Cubalider\Component\Sms\Entity\Message'
    )
    {
        $this->em = $em;
        $this->class = $em->getClassMetadata($class)->getName();
        $this->repository = $this->em->getRepository($class);
    }

    /**
     * Pushes given messages into a new bulk
     *
     * @param MessageInterface[] $messages
     * @return void
     */
    public function push($messages)
    {
        if (0 < count($messages)) {
            $bulk = new Bulk();
            $this->em->persist($bulk);

            foreach ($messages as $message) {
                $message->setBulk($bulk);
                $this->em->persist($message);
            }

            $this->em->flush();
        }
    }

    /**
     * Pops from the given bulk, the given amount of messages
     * If no amount is set, it pops all messages
     * If the bulk is empty, it deletes the bulk and returns false
     *
     * @param BulkInterface $bulk
     * @param integer       $amount
     * @return MessageInterface[]
     */
    public function pop($bulk, $amount = null)
    {
        $queryBuilder = $this->repository
            ->createQueryBuilder('M')
            ->where('M.bulk = :bulk');
        
        if (null !== $amount) {
            $queryBuilder->setMaxResults($amount);
        }

        $queryBuilder->setParameter('bulk', $bulk);

        $messages = $queryBuilder
            ->getQuery()
            ->getResult();

        if (0 == count($messages)) {
            $this->em->remove($bulk);
            $this->em->flush();

            return false;
        }

        foreach ($messages as $message) {
            $this->em->remove($message);
        }

        $this->em->flush();

        return $messages;
    }
}