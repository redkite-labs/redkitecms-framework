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
            ->disableOriginalConstructor()
            ->getMock()
        ;
    }

    public function testRoutingInstantiation()
    {
        $isProduction = true;
        $this->configurationHandler
            ->expects($this->once())
            ->method('isProduction')
            ->will($this->returnValue($isProduction))
        ;

        $router = Routing::create($this->configurationHandler);
        $this->assertInstanceOf('\RedKiteCms\Bridge\Routing\RoutingFrontend', $router);
    }

}
