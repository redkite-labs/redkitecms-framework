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

namespace RedKiteCms\Action\Block;

use RedKiteCms\TestCase;

/**
 * Class TestBaseAction
 *
 * @author RedKite Labs <webmaster@redkite-labs.com>
 */
abstract class TestBaseAction extends TestCase
{
    protected $app = null;
    protected $siteDir = 'app/data/mysite.com';

    abstract protected function initBlockManager($siteDir, $options, $username);

    protected function boot($options, $username, $factoryMethod)
    {
        $configurationHandler = $this->initConfigurationHandler();
        $blockManagerFactory = $this->initBlockManagerFactory($this->siteDir, $options, $username, $factoryMethod);
        $this->initApp($configurationHandler, $blockManagerFactory);
    }

    protected function normalizeOptions(array $options)
    {
        $options = $options["data"];
        $options["blockname"] = $options["name"];
        unset($options["name"]);

        return $options;
    }

    private function initApp($configurationHandler, $blockManagerFactory)
    {
        $this->app = $this
            ->getMockBuilder('\Silex\Application')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->app
            ->expects($this->at(0))
            ->method('offsetGet')
            ->with('red_kite_cms.blocks_manager_factory')
            ->will($this->returnValue($blockManagerFactory));
        ;

        $this->app
            ->expects($this->at(1))
            ->method('offsetGet')
            ->with('red_kite_cms.configuration_handler')
            ->will($this->returnValue($configurationHandler));
        ;
    }

    private function initConfigurationHandler()
    {
        $configurationHandler = $this
            ->getMockBuilder('\RedKiteCms\Configuration\ConfigurationHandler')
            ->disableOriginalConstructor()
            ->setMethods(array('siteDir'))
            ->getMock()
        ;

        $configurationHandler
            ->expects($this->once())
            ->method('siteDir')
            ->will($this->returnValue($this->siteDir));
        ;

        return $configurationHandler;
    }

    private function initBlockManagerFactory($siteDir, $options, $username, $factoryMethod)
    {
        $blockManager = $this->initBlockManager($siteDir, $options, $username);
        $blockManagerFactory = $this
            ->getMockBuilder('\RedKiteCms\Content\BlockManager\BlockManagerFactory')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $blockManagerFactory
            ->expects($this->once())
            ->method('create')
            ->with($factoryMethod)
            ->will($this->returnValue($blockManager));
        ;

        return $blockManagerFactory;
    }
}
