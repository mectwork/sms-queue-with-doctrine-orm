<?php

namespace Cubalider\Component\Sms\Manager;

use Cubalider\Component\Sms\Model\Bulk;
use Cubalider\Component\Sms\Model\Message;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMInvalidArgumentException;
use Yosmanyga\Component\Dql\Fit\AndFit;
use Yosmanyga\Component\Dql\Fit\Builder;
use Yosmanyga\Component\Dql\Fit\LimitFit;
use Yosmanyga\Component\Dql\Fit\SelectCountFit;
use Yosmanyga\Component\Dql\Fit\WhereCriteriaFit;


/**
 * @author Yosmany Garcia <yosmanyga@gmail.com>
 * @author Yusliel Garcia <yuslielg@gmail.com>
 */
class MessageManager implements MessageManagerInterface
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $class = 'Cubalider\Component\Sms\Model\Message';

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $em;

    /**
     * @var Builder;
     */
    private $builder;

    /**
     * @var \Cubalider\Component\Sms\Manager\BulkManagerInterface
     */
    private $bulkManager;


    /**
     * @param EntityManagerInterface $em
     * @param Builder $builder
     * @param BulkManagerInterface $bulkManager
     */
    public function __construct(EntityManagerInterface $em, Builder $builder = null, BulkManagerInterface $bulkManager = null)
    {
        $this->em = $em;
        $this->builder = $builder ? : new Builder($em);
        $this->bulkManager = $bulkManager ? : new BulkManager($em);
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
            /** @var \Cubalider\Component\Sms\Model\Bulk $bulk */
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
        /** @var \Cubalider\Component\Sms\Model\Bulk $bulk */
        $bulk = $this->bulkManager->approach();

        $dq = $this->builder->build(
            $this->class,
            new AndFit(array(
                new WhereCriteriaFit(array('bulk' => $bulk->getId())),
                new LimitFit($amount)
            ))
        );

        $messages = $dq->getQuery()->getResult();

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
        $count = false;
        $dq = $this->builder->build(
            $this->class,
            new AndFit(array(
                new SelectCountFit('id'),
                new WhereCriteriaFit(array('bulk' => $bulk->getId()))
            ))
        );

        try {
            $count = $dq->getQuery()->getSingleScalarResult();
        } catch (ORMInvalidArgumentException $e) {
        }

        return $count;
    }
}
