<?php

namespace Muchacuba\Test\Component\Sms\Entity;

use Muchacuba\Component\Sms\Entity\Bulk;

/**
 * @author Yusliel Garcia <yuslielg@gmail.com>
 */
class BulkTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers \Muchacuba\Component\Sms\Entity\Bulk::getId
     */
    public function testId()
    {
        $bulk = new Bulk();
        $this->assertNull($bulk->getId());
    }

    
    /**
     * @covers \Muchacuba\Component\Sms\Entity\Bulk::setPosition
     * @covers \Muchacuba\Component\Sms\Entity\Bulk::getPosition
     */
    public function testPosition()
    {
        $bulk = new Bulk();
        $this->assertNull($bulk->getPosition());

        $bulk->setPosition(2);
        $this->assertEquals(2, $bulk->getPosition());
    }
}