<?php

namespace Cubalider\Component\Sms\Manager\Fit;

use Doctrine\ORM\Query\Expr;
use Yosmanyga\Component\Dql\Fit\OrderFitInterface;

class OrderQueueFit implements OrderFitInterface
{
    public function getOrder($alias)
    {
        return new Expr\OrderBy(
            sprintf("%s.position", $alias)
        );
    }
}