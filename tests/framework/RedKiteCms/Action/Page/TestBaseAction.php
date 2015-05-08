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

namespace RedKiteCms\Action\Page;

use RedKiteCms\TestCase;

/**
 * Class TestBaseAction
 *
 * @author RedKite Labs <webmaster@redkite-labs.com>
 */
abstract class TestBaseAction extends TestCase
{
    protected $app = null;

    abstract protected function configurePageManagerCollection($pageManagerCollection, $values, $username);

    protected function boot(array $values, $username)
    {
        $pageManagerCollection = $this
            ->getMockBuilder('\RedKiteCms\Content\PageCollection\PageCollectionManager')
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $this->configurePageManagerCollection($pageManagerCollection, $values, $username);
        $this->initApp($pageManagerCollection);
    }

    protected function initApp($pageManagerCollection)
    {
        $this->app = $this
            ->getMockBuilder('\Silex\Application')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->app
            ->expects($this->at(0))
            ->method('offsetGet')
            ->with('red_kite_cms.page_collection_manager')
            ->will($this->returnValue($pageManagerCollection));
        ;
    }
}
