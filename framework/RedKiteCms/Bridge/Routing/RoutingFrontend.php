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

namespace RedKiteCms\Bridge\Routing;

use RedKiteCms\Bridge\Routing\RoutingGenerator;
use RedKiteCms\Configuration\ConfigurationHandler;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Routing\Router;

/**
 * This object instantiates a new routing for the frontend environment
 *
 * @author  RedKite Labs <webmaster@redkite-labs.com>
 * @package RedKiteCms\Bridge\Routing
 */
class RoutingFrontend extends RoutingBase
{
    /**
     * {@inheritdoc}
     */
    protected $routesFile = 'routes-frontend.yml';

    /**
     * {@inheritdoc}
     */
    public function generateWebsiteRoutes(RoutingGenerator $routingGenerator, $user = null)
    {
        $routingGenerator
            ->pattern('/')
            ->frontController('Controller\Cms\FrontendController::showAction')
            ->generate($this->router)
        ;
    }
} 