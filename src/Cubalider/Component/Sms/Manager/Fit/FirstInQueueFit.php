<?php

namespace Cubalider\Component\Sms\Manager\Fit;

use Doctrine\ORM\Query\Expr;
use Yosmanyga\Component\Dql\Fit\LimitFitInterface;

class FirstInQueueFit implements LimitFitInterface
{
    public function getLimit()
    {
        return 1;
    }
}