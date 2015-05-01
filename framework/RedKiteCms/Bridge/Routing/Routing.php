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


use RedKiteCms\Bridge\Routing\RoutingBackend;
use RedKiteCms\Bridge\Routing\RoutingFrontend;
use RedKiteCms\Configuration\ConfigurationHandler;

/**
 * This object defines the base class to instantiate a new routing environment
 *
 * @author  RedKite Labs <webmaster@redkite-labs.com>
 * @package RedKiteCms\Bridge\Routing
 */
class Routing
{
    /**
     * @type null|\RedKiteCms\Bridge\Routing\RoutingBase
     */
    private static $routing = null;

    /**
     * @param \RedKiteCms\Configuration\ConfigurationHandler $configurationHandler
     * @param bool $debug
     *
     * @return null|\RedKiteCms\Bridge\Routing\RoutingBase
     */
    public static function create(ConfigurationHandler $configurationHandler, $debug = false)
    {
        if (null === self::$routing) {
            self::$routing = self::initRouter($configurationHandler);
            self::$routing ->createRouter($debug);
        }

        return self::$routing;
    }

    private static function initRouter(ConfigurationHandler $configurationHandler)
    {
        if ($configurationHandler->isProduction()) {

            return new RoutingFrontend($configurationHandler);
        }

        return new RoutingBackend($configurationHandler);
    }
} 