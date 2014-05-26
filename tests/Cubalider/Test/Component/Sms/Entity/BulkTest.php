<?php

namespace Cubalider\Test\Component\Sms\Entity;

use Cubalider\Component\Sms\Entity\Bulk;

/**
 * @author Yusliel Garcia <yuslielg@gmail.com>
 */
class BulkTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers \Cubalider\Component\Sms\Entity\Bulk::getId
     */
    public function testId()
    {
        $bulk = new Bulk();
        $this->assertNull($bulk->getId());
    }

    
    /**
     * @covers \Cubalider\Component\Sms\Entity\Bulk::setPosition
     * @covers \Cubalider\Component\Sms\Entity\Bulk::getPosition
     */
    public function testPosition()
    {
        $bulk = new Bulk();
        $this->assertNull($bulk->getPosition());

        $bulk->setPosition(2);
        $this->assertEquals(2, $bulk->getPosition());
    }
}