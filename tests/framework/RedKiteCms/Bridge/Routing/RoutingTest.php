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
use RedKiteCms\Bridge\Routing\Routing;
use RedKiteCms\Bridge\Routing\RoutingFrontend;
use RedKiteCms\TestCase;

class RoutingTester extends Routing
{
    public static function clear()
    {
        self::$routing = null;
    }
}

/**
 * Class RoutingTest
 *
 * @author RedKite Labs <webmaster@redkite-labs.com>
 */
class RoutingTest extends TestCase
{
    private $configurationHandler;

    protected function setUp()
    {
        $this->configurationHandler = $this
            ->getMockBuilder('\RedKiteCms\Configuration\ConfigurationHandler')
            ->setMethods(array('isProduction'))
            ->disableOriginalConstructor()
            ->getMock()
        ;
    }

    /**
     * @dataProvider routingProvider
     */
    public function testRoutingInstantiation($isProduction, $expectedObjectClass)
    {
        $this->configurationHandler
            ->expects($this->exactly(2))
            ->method('isProduction')
            ->will($this->returnValue($isProduction))
        ;

        RoutingTester::clear();
        $router = RoutingTester::create($this->configurationHandler);
        $this->assertInstanceOf($expectedObjectClass, $router);
        $this->assertInstanceOf($expectedObjectClass, Routing::getRouting());
    }

    public function routingProvider()
    {
        return array(
            array(
                true,
                '\RedKiteCms\Bridge\Routing\RoutingFrontend',
            ),
            array(
                false,
                '\RedKiteCms\Bridge\Routing\RoutingBackend',
            ),
        );
    }
}
