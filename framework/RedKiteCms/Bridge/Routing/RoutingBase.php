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

use RedKiteCms\Configuration\ConfigurationHandler;
use RedKiteCms\Exception\General\LogicException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Routing\Router;

/**
 * This object defines the base class to handle a routing environment
 *
 * @author  RedKite Labs <webmaster@redkite-labs.com>
 * @package RedKiteCms\Bridge\Routing
 */
abstract class RoutingBase
{
    /**
     * @type \RedKiteCms\Configuration\ConfigurationHandler
     */
    protected $configurationHandler;
    /**
     * @type null|string
     */
    protected $routesFile = null;
    /**
     * @type \Symfony\Component\Routing\Router
     */
    protected $router = null;
    /**
     * @type \Symfony\Component\Config\FileLocator
     */
    private $fileLocator;

    /**
     * Generates the website page routes for the current environment
     *
     * @param \RedKiteCms\Bridge\Routing\RoutingGenerator $routingGenerator
     * @param null|string $user
     *
     * @return mixed
     */
    abstract public function generateWebsiteRoutes(RoutingGenerator $routingGenerator, $user = null);

    /**
     * Constructor
     *
     * @param \RedKiteCms\Configuration\ConfigurationHandler $configurationHandler
     * @param \Symfony\Component\Config\FileLocator $fileLocator
     */
    public function __construct(ConfigurationHandler $configurationHandler, FileLocator $fileLocator = null)
    {
        $this->configurationHandler = $configurationHandler;
        if (null === $fileLocator) {
            $this->fileLocator = new FileLocator(array($this->configurationHandler->coreConfigDir()));
        }
    }

    /**
     * @return null|\Symfony\Component\Routing\Router
     */
    public function getRouter()
    {
        return $this->router;
    }

    /**
     * Creates the router
     *
     * @param bool $debug
     *
     * @return null|\Symfony\Component\Routing\Router
     */
    public function createRouter($debug = false)
    {
        if (null === $this->routesFile) {
            throw new LogicException('The derived class must define the string variable "routesFile"');
        }

        if (!is_string($this->routesFile)) {
            throw new LogicException('"routesFile" variable must be a string value');
        }

        $isProduction = $this->configurationHandler->isProduction();

        $cacheDir = null;
        if (!$debug && $isProduction) {
            $cacheDir = $this->configurationHandler->siteCacheDir() . '/routes';
        }

        $this->router = new Router(
            new YamlFileLoader($this->fileLocator),
            $this->routesFile,
            array('cache_dir' => $cacheDir)
        );

        return $this->router;
    }
} 