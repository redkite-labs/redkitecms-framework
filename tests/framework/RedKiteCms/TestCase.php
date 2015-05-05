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

namespace RedKiteCms;

use RedKiteCms\Bridge\Dispatcher\Dispatcher;
use RedKiteCms\Bridge\Monolog\DataLogger;

abstract class TestCase extends \PHPUnit_Framework_TestCase
{
    protected $dispatcher;
    protected $logger;

    protected function setUp()
    {
        $this->dispatcher = $this->getMock('\Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $this->logger = $this->getMock('\Psr\Log\LoggerInterface');

        DataLogger::init($this->logger);
        Dispatcher::setDispatcher($this->dispatcher);
    }

    protected function dispatch($at, $eventName, $eventClass)
    {
        $this->dispatcher
            ->expects($this->at($at))
            ->method('dispatch')
            ->with($eventName, $this->isInstanceOf($eventClass))
        ;
    }

    protected function log($at, $method, $text)
    {
        $this->logger
            ->expects($this->at($at))
            ->method($method)
            ->with($text)
        ;
    }
}
