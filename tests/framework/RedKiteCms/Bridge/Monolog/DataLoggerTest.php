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
use RedKiteCms\Bridge\Monolog\DataLogger;
use RedKiteCms\TestCase;

/**
 * Class DispatcherTest
 *
 * @author RedKite Labs <webmaster@redkite-labs.com>
 */
class DataLoggerTest extends TestCase
{
    private $dataLogger;

    protected function setUp()
    {
        $this->dataLogger = $this->getMock('\Psr\Log\LoggerInterface');
    }

    public function testMessageIsNotLoggedWhenAnyLoggerHasBeenAssigned()
    {
        $this->dataLogger
            ->expects($this->never())
            ->method('info')
        ;

        DataLogger::init(null);
        DataLogger::log('foo');
    }

    public function testMessageIsLogged()
    {
        $message = 'foo';
        $this->dataLogger
            ->expects($this->once())
            ->method('info')
            ->with($message)
        ;

        DataLogger::init($this->dataLogger);
        DataLogger::log($message);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Logger does not support the bar method.
     */
    public function testEventAborted()
    {
        $message = 'foo';
        $method = 'bar';
        $this->dataLogger
            ->expects($this->never())
            ->method($method)
        ;

        DataLogger::init($this->dataLogger);
        DataLogger::log($message, $method);
    }
}
