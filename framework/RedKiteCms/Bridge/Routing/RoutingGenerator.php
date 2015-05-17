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
use RedKiteCms\Tools\FilesystemTools;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\Router;
use Symfony\Component\Routing\RouterInterface;

/**
 * This object is assigned to generate dynamically the website page routes
 *
 * @author  RedKite Labs <webmaster@redkite-labs.com>
 * @package RedKiteCms\Bridge\Routing
 */
class RoutingGenerator
{
    /**
     * @type ConfigurationHandler
     */
    private $configurationHandler;
    /**
     * @type string
     */
    private $pattern = null;
    /**
     * @type string
     */
    private $frontController = null;
    /**
     * @type string
     */
    private $contributor = null;
    /**
     * @type string
     */
    private $explicitHomepageRoute = false;
    /**
     * @type array
     */
    private $routes = array(
        "homepage" => "",
        "pages" => array(),
    );

    /**
     * Constructor
     *
     * @param ConfigurationHandler $configurationHandler
     */
    public function __construct(ConfigurationHandler $configurationHandler)
    {
        $this->configurationHandler = $configurationHandler;
    }

    /**
     * @return array
     */
    public function getRoutes()
    {
        return $this->routes;
    }


    /**
     * Sets the route pattern
     *
     * @param $pattern
     * @return $this
     */
    public function pattern($pattern)
    {
        $this->pattern = $pattern;

        return $this;
    }

    /**
     * Sets the frontcontroller that handles the route
     *
     * @param $frontController
     * @return $this
     */
    public function frontController($frontController)
    {
        $this->frontController = $frontController;

        return $this;
    }

    /**
     * Sets the contributer user
     *
     * @param $contributor
     * @return $this
     */
    public function contributor($contributor)
    {
        $this->contributor = $contributor;

        return $this;
    }

    /**
     * Generates the homepage route when true
     *
     * @param $value
     * @return $this
     */
    public function explicitHomepageRoute($value)
    {
        $this->explicitHomepageRoute = (bool)$value;

        return $this;
    }

    /**
     * Generates the routes
     *
     * @return array
     */
    public function generate(RouterInterface $router)
    {
        $routes = $router->getRouteCollection();
        $pagesDir = $this->configurationHandler->pagesDir();
        $homepageValues = array(
            '_locale' => $this->configurationHandler->language(),
            'country' => $this->configurationHandler->country(),
            'page' => $this->configurationHandler->homepage(),
        );

        $homeRouteName = '_home_' . $homepageValues["_locale"] . '_' . $homepageValues["country"] . '_' . $homepageValues["page"];
        $this->routes["homepage"] = $homeRouteName;
        if ($this->explicitHomepageRoute) {
            $values = array_merge($homepageValues, array('_controller' => $this->frontController,));
            $routes->add($homeRouteName, new Route($this->pattern, $values));
        }

        $seoFileName = 'seo.json';
        if (null !== $this->contributor) {
            $seoFileName = $this->contributor . '.json';
        }

        $finder = new Finder();
        $pages = $finder->directories()->depth(0)->in($pagesDir);
        foreach ($pages as $page) {
            $this->generateLanguagesRoutes($routes, $page, $seoFileName);
        }
    }

    private function generateLanguagesRoutes($routes, $page, $seoFileName)
    {
        $page = (string)$page;
        $pageName = basename($page);

        $languagesFinder = new Finder();
        $languages = $languagesFinder->directories()->depth(0)->in($page);
        foreach ($languages as $language) {
            $language = (string)$language;
            $seoFile = $language . '/' . $seoFileName;
            if (!file_exists($seoFile)) {
                continue;
            }

            $languageName = basename($language);
            $languageTokens = explode('_', $languageName);
            $routeName = '_' . $languageName . '_' . $pageName;
            $values = array(
                '_locale' => $languageTokens[0],
                'country' => $languageTokens[1],
                'page' => $pageName,
            );
            $this->routes["pages"][] = $routeName;

            $pattern = $this->pattern;
            if (substr($pattern, -1) != '/') {
                $pattern .= '/';
            }
            $pageValues = json_decode(FilesystemTools::readFile($seoFile), true);
            $this->addChangedPermalinks($routes, $routeName, $pattern, $pageValues);

            $values = array_merge($values, array('_controller' => $this->frontController,));
            $routes->add($routeName, new Route($pattern . $pageValues["permalink"], $values));
        }
    }

    private function addChangedPermalinks($routes, $routeName, $pattern, $values)
    {
        if (!array_key_exists("changed_permalinks", $values)) {
            return null;
        }

        foreach ($values["changed_permalinks"] as $permalink) {
            $removeRouteName = '_removed_' . $permalink;
            $routeParams = array(
                'route_name' => $routeName,
                '_controller' => 'Controller\Cms\MissingPermalinkController::redirectAction',
            );
            $routes->add($removeRouteName, new Route($pattern . $permalink, $routeParams));
        }
    }
}