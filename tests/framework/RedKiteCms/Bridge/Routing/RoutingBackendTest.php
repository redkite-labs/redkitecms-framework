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

use RedKiteCms\Bridge\Routing\RoutingBackend;
use RedKiteCms\TestCase;

class RouterBackendTest extends RoutingBackend
{
    public function getRoutesFile()
    {
        return $this->routesFile;
    }
}

/**
 * Class RoutingFrontendTest
 *
 * @author RedKite Labs <webmaster@redkite-labs.com>
 */
class RoutingBackendTest extends TestCase
{
    private $configurationHandler;
    private $routingGenerator;

    protected function setUp()
    {
        $this->configurationHandler = $this
            ->getMockBuilder('\RedKiteCms\Configuration\ConfigurationHandler')
            ->setMethods(array('isProduction', 'coreConfigDir', 'siteCacheDir'))
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->routingGenerator = $this
            ->getMockBuilder('\RedKiteCms\Bridge\Routing\RoutingGenerator')
            ->disableOriginalConstructor()
            ->getMock()
        ;
    }

    public function testRoutesFileValue()
    {
        $router = new RouterBackendTest($this->configurationHandler);
        $this->assertEquals('routes-backend.yml', $router->getRoutesFile());
    }

    public function testRouterCreation()
    {
        $user = 'admin';
        $backendRouter = new RoutingBackend($this->configurationHandler);
        $backendRouter->createRouter();

        $this->routingGenerator
            ->expects($this->once())
            ->method('pattern')
            ->with('/backend')
            ->will($this->returnSelf())
        ;

        $this->routingGenerator
            ->expects($this->once())
            ->method('frontController')
            ->with('Controller\Cms\BackendController::showAction')
            ->will($this->returnSelf())
        ;

        $this->routingGenerator
            ->expects($this->once())
            ->method('contributor')
            ->with($user)
            ->will($this->returnSelf())
        ;

        $this->routingGenerator
            ->expects($this->once())
            ->method('generate')
            ->with($backendRouter->getRouter())
        ;

        $backendRouter->generateWebsiteRoutes($this->routingGenerator, $user);
    }
}
