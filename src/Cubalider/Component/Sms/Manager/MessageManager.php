<?php

namespace Cubalider\Component\Sms\Manager;

use Doctrine\ORM\EntityManagerInterface;
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
     * @var \Doctrine\ORM\EntityRepository
     */
    private $repository;

    /**
     * @var string
     */
    private $class;

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
     * @param string                 $class
     * @param BulkManagerInterface   $bulkManager
     */
    public function __construct(
        EntityManagerInterface $em,
        $class = 'Cubalider\Component\Sms\Entity\Message',
        BulkManagerInterface $bulkManager = null
    )
    {
        $this->em = $em;
        $this->repository = $this->em->getRepository($class);
        $this->class = $em->getClassMetadata($class)->getName();
        $this->bulkManager = $bulkManager ?: new BulkManager($em);
    }

    /**
     * Pushes messages using given messages.
     *
     * @api
     * @param MessageInterface[] $messages
     * @throws \Exception if there is any problem with the transaction
     * @return BulkInterface|false
     */
    public function push($messages)
    {
        if (0 == count($messages)) {
            return false;
        }

        $this->em->beginTransaction();
        try {
            $bulk = $this->bulkManager->push();

            $class = $this->class;
            foreach ($messages as $message) {
                /** @var MessageInterface $internalMessage */
                $internalMessage = new $class;
                $internalMessage->setBulk($bulk);
                $internalMessage->setSender($message->getSender());
                $internalMessage->setReceiver($message->getReceiver());
                $internalMessage->setText($message->getText());
                $this->em->persist($internalMessage);
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
     * @param integer       $amount
     * @return MessageInterface[]|null
     */
    public function pop($amount)
    {
        $bulk = $this->bulkManager->approach();

        $messages = $this->findMessages($bulk, $amount);

        if ($messages) {
            $this->removeMessages($messages);
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
     * @param BulkInterface $bulk
     * @return int|false
     */
    public function estimate($bulk)
    {
        /** @var \Doctrine\ORM\AbstractQuery $query */
        $query = $this->em->createQuery(sprintf(
            "SELECT COUNT(M) FROM %s M WHERE M.bulk = :bulk", $this->class
        ));

        return $query
            ->setParameter('bulk', $bulk)
            ->getSingleScalarResult();
    }

    /**
     * Finds given amount of messages belonging to given bulk.
     *
     * @param BulkInterface $bulk
     * @param int           $amount
     * @return MessageInterface[]|null
     */
    private function findMessages($bulk, $amount)
    {
        return $this->repository
            ->createQueryBuilder('M')
            ->where('M.bulk = :bulk')
            ->setMaxResults($amount)
            ->setParameter('bulk', $bulk)
            ->getQuery()
            ->getResult();
    }

    /**
     * Removes messages.
     *
     * @param MessageInterface[] $messages
     */
    private function removeMessages($messages)
    {
        foreach ($messages as $message) {
            $this->em->remove($message);
        }
    }
}