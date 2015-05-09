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
use RedKiteCms\Bridge\Routing\RoutingBase;
use RedKiteCms\Bridge\Routing\RoutingFrontend;
use RedKiteCms\Bridge\Routing\RoutingGenerator;
use RedKiteCms\TestCase;

class WrongRouter1 extends RoutingBase
{
    public function generateWebsiteRoutes(RoutingGenerator $routingGenerator, $user = null)
    {
        return array();
    }
}

class WrongRouter2 extends RoutingBase
{
    protected $routesFile = array('routes-frontend.yml');

    public function generateWebsiteRoutes(RoutingGenerator $routingGenerator, $user = null)
    {
        return array();
    }
}

class Router extends RoutingBase
{
    protected $routesFile = 'routes.yml';

    public function generateWebsiteRoutes(RoutingGenerator $routingGenerator, $user = null)
    {
        return array();
    }
}

/**
 * Class RoutingBaseTest
 *
 * @author RedKite Labs <webmaster@redkite-labs.com>
 */
class RoutingBaseTest extends TestCase
{
    private $configurationHandler;

    protected function setUp()
    {
        $this->configurationHandler = $this
            ->getMockBuilder('\RedKiteCms\Configuration\ConfigurationHandler')
            ->setMethods(array('isProduction', 'coreConfigDir', 'siteCacheDir'))
            ->disableOriginalConstructor()
            ->getMock()
        ;
    }

    /**
     * @expectedException \RedKiteCms\Exception\General\LogicException
     * @expectedExceptionMessage The derived class must define the string variable "routesFile"
     */
    public function testRoutingDerivedMustDefineRoutesFileVariable()
    {
        $router = new WrongRouter1($this->configurationHandler);
        $router->createRouter();
    }

    /**
     * @expectedException \RedKiteCms\Exception\General\LogicException
     * @expectedExceptionMessage "routesFile" variable must be a string value
     */
    public function testRoutesFileVariableMustBeAString()
    {
        $router = new WrongRouter2($this->configurationHandler);
        $router->createRouter();
    }

    /**
     * @dataProvider routingProvider
     */
    public function testRouterCreation($isProduction, $debug)
    {
        $this->configurationHandler
            ->expects($this->once())
            ->method('coreConfigDir')
        ;

        $this->configurationHandler
            ->expects($this->once())
            ->method('isProduction')
            ->will($this->returnValue($isProduction))
        ;

        $times = (int)(!$debug && $isProduction);
        $this->configurationHandler
            ->expects($this->exactly($times))
            ->method('siteCacheDir')
            ->will($this->returnValue($isProduction))
        ;

        $wrapperRouter = new Router($this->configurationHandler);
        $router = $wrapperRouter->createRouter($debug);
        $this->assertInstanceOf('Symfony\Component\Routing\Router', $router);
        $this->assertEquals($router, $wrapperRouter->getRouter());
    }

    public function routingProvider()
    {
        return array(
            array(
                true,
                true,
            ),
            array(
                true,
                false,
            ),
            array(
                false,
                true,
            ),
            array(
                false,
                false,
            ),
        );
    }
}
