<?php
/**
 * This file is part of the RedKite CMS Application and it is distributed
 * under the GPL LICENSE Version 2.0. To use this application you must leave
 * intact this copyright notice.
 *
 * Copyright (c) RedKite Labs <info@redkite-labs.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * For extra documentation and help please visit http://www.redkite-labs.com
 *
 * @license    GPL LICENSE Version 2.0
 *
 */

namespace RedKiteCms\Bridge\Assetic;

use RedKiteCms\Bridge\Dispatcher\Dispatcher;
use RedKiteCms\TestCase;

/**
 * Class DispatcherTest
 *
 * @author RedKite Labs <webmaster@redkite-labs.com>
 */
class DispatcherTest extends TestCase
{
    private $eventDispatcher;

    protected function setUp()
    {
        $this->eventDispatcher = $this->getMock('\Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $this->event = $this
            ->getMockBuilder('RedKiteCms\EventSystem\Event\Block\BlockAddedEvent')
            ->disableOriginalConstructor()
            ->getMock()
        ;
    }

    public function testEventIsNotDispatchedWhenAnyDispatcherHasBeenAssigned()
    {
        $this->eventDispatcher
            ->expects($this->never())
            ->method('dispatch')
        ;

        Dispatcher::setDispatcher(null);
        $this->assertEquals($this->event, Dispatcher::dispatch('foo.event', $this->event));
    }

    public function testEventIsDispatched()
    {
        $eventName = 'foo.event';
        $this->eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($eventName, $this->event)
        ;

        $this->event
            ->expects($this->once())
            ->method('getAbort')

            ->will($this->returnValue(false))
        ;

        Dispatcher::setDispatcher($this->eventDispatcher);
        $this->assertEquals($this->event, Dispatcher::dispatch($eventName, $this->event));
    }


    /**
     * @expectedException \RedKiteCms\Exception\Event\EventAbortedException
     * @expectedExceptionMessage abort message
     */
    public function testEventAborted()
    {
        $eventName = 'foo.event';
        $this->eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($eventName, $this->event)
        ;

        $this->event
            ->expects($this->once())
            ->method('getAbort')
            ->will($this->returnValue(true))
        ;
        $this->event
            ->expects($this->once())
            ->method('getAbortMessage')
            ->will($this->returnValue("abort message"))
        ;

        Dispatcher::setDispatcher($this->eventDispatcher);
        $this->assertEquals($this->event, Dispatcher::dispatch($eventName, $this->event));
    }
}
