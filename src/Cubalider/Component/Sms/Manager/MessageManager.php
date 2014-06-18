<?php

namespace Cubalider\Component\Sms\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Cubalider\Component\Sms\Model\Bulk;
use Cubalider\Component\Sms\Model\Message;
use Doctrine\ORM\ORMInvalidArgumentException;

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
     * @var \Doctrine\ORM\EntityRepository
     */
    private $repository;

    /**
     * @var BulkManagerInterface
     */
    private $bulkManager;

    /**
     * Constructor
     *
     * Additionally it creates a repository using $em, for given class
     *
     * @param EntityManagerInterface $em
     * @param BulkManagerInterface   $bulkManager
     */
    public function __construct(
        EntityManagerInterface $em,
        BulkManagerInterface $bulkManager = null
    )
    {
        $this->em = $em;
        $this->repository = $this->em->getRepository('Cubalider\Component\Sms\Model\Message');
        $this->bulkManager = $bulkManager ?: new BulkManager($em);
    }

    /**
     * @inheritdoc
     */
    public function push($messages)
    {
        if (0 == count($messages)) {
            return false;
        }

        $this->em->beginTransaction();
        try {
            $bulk = $this->bulkManager->push();

            foreach ($messages as $message) {
                $message->setBulk($bulk);
                $this->em->persist($message);
            }

            $this->em->flush();
            $this->em->commit();
        } catch (\Exception $e) {
            $this->em->rollback();

            throw $e;
        }

        return $bulk;
    }

    /**
     * Pops given amount of messages.
     * It removes all returned messages.
     * If no messages is found it returns null.
     *
     * @param integer $amount
     * @return Message[]|null
     */
    public function pop($amount)
    {
        $bulk = $this->bulkManager->approach();

        $messages = $this->repository
            ->createQueryBuilder('M')
            ->where('M.bulk = :bulk')
            ->setMaxResults($amount)
            ->setParameter('bulk', $bulk)
            ->getQuery()
            ->getResult();

        if ($messages) {
            foreach ($messages as $message) {
                $this->em->remove($message);
            }
        }

        if (!$messages) {
            $this->bulkManager->pop();
        }

        $this->em->flush();

        return $messages;
    }

    /**
     * Returns the amount of messages remaining for given bulk.
     * It returns false if bulk doest' exist
     *
     * @param Bulk $bulk
     * @return int|false
     */
    public function estimate(Bulk $bulk)
    {
        /** @var \Doctrine\ORM\AbstractQuery $query */
        $query = $this->em
            ->createQuery('
                SELECT COUNT(M)
                FROM Cubalider\Component\Sms\Model\Message M
                WHERE M.bulk = :bulk
            ');
        $query->setParameter('bulk', $bulk);

        try {
            $count = $query->getSingleScalarResult();
        } catch (ORMInvalidArgumentException $e) {
            $count = false;
        }

        return $count;
    }
}