<?php
/**
 * This file is part of the RedKite CMS Application and it is distributed
 * under the MIT License. To use this application you must leave
 * intact this copyright notice.
 *
 * Copyright (c) RedKite Labs <webmaster@redkite-labs.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * For extra documentation and help please visit http://www.redkite-labs.com
 *
 * @license    MIT License
 *
 */

namespace RedKiteCms\Content\PageCollection;

use org\bovigo\vfs\vfsStream;
use RedKiteCms\Bridge\Dispatcher\Dispatcher;
use RedKiteCms\Content\BlockManager\BlockManagerApprover;
use RedKiteCms\Content\Page\PageManager;
use RedKiteCms\TestCase;

/**
 * BasePagesTest
 *
 * @author RedKite Labs <webmaster@redkite-labs.com>
 */
abstract class BasePagesTest extends TestCase
{
    protected function initConfigurationHandler()
    {
        $configurationHandler = $this
            ->getMockBuilder('\RedKiteCms\Configuration\ConfigurationHandler')
            ->disableOriginalConstructor()
            ->setMethods(array('siteDir', 'pagesRootDir', 'pagesDir', 'pagesRemovedDir', 'homepage'))
            ->getMock()
        ;

        $configurationHandler
            ->expects($this->once())
            ->method('siteDir')
            ->will($this->returnValue(vfsStream::url('localhost')));
        ;

        $configurationHandler
            ->expects($this->once())
            ->method('pagesRootDir')
            ->will($this->returnValue(vfsStream::url('localhost/pages')));
        ;

        $configurationHandler
            ->expects($this->once())
            ->method('pagesDir')
            ->will($this->returnValue(vfsStream::url('localhost/pages/pages')));
        ;

        $configurationHandler
            ->expects($this->once())
            ->method('pagesRemovedDir')
            ->will($this->returnValue(vfsStream::url('localhost/pages/removed')));
        ;

        return $configurationHandler;
    }

    protected function initDispatcherAndLogger($events, $logs)
    {
        $at = 0;
        foreach($events as $eventName => $eventClass) {
            $this->dispatch($at, $eventName, $eventClass);
            $at++;
        }
        $at = 0;
        foreach($logs as $logMessage) {
            $this->log($at, 'info', $logMessage);
            $at++;
        }
    }
}