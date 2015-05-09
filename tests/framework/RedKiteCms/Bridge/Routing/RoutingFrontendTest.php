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

use RedKiteCms\Bridge\Routing\RoutingFrontend;
use RedKiteCms\TestCase;

class RouterFrontendTest extends RoutingFrontend
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
class RoutingFrontendTest extends TestCase
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
        $router = new RouterFrontendTest($this->configurationHandler);
        $this->assertEquals('routes-frontend.yml', $router->getRoutesFile());
    }

    public function testRouterCreation()
    {
        $frontendRouter = new RoutingFrontend($this->configurationHandler);
        $frontendRouter->createRouter();

        $this->routingGenerator
            ->expects($this->once())
            ->method('pattern')
            ->with('/')
            ->will($this->returnSelf())
        ;

        $this->routingGenerator
            ->expects($this->once())
            ->method('frontController')
            ->with('Controller\Cms\FrontendController::showAction')
            ->will($this->returnSelf())
        ;

        $this->routingGenerator
            ->expects($this->once())
            ->method('explicitHomepageRoute')
            ->with(true)
            ->will($this->returnSelf())
        ;

        $this->routingGenerator
            ->expects($this->once())
            ->method('generate')
            ->with($frontendRouter->getRouter())
        ;

        $frontendRouter->generateWebsiteRoutes($this->routingGenerator);
    }
}
