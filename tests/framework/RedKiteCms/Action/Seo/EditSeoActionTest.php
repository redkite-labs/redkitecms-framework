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

namespace RedKiteCms\Action\Seo;

use RedKiteCms\TestCase;

/**
 * Class EditSeoActionTest
 *
 * @author RedKite Labs <webmaster@redkite-labs.com>
 */
class EditSeoActionTest extends TestCase
{
    private $app = null;

    public function testEditSeo()
    {
        $username = 'john';
        $values = array(
            'data' => array(
                'pageName' => 'index',
                'seoData' => array(
                    "foo" => 'bar',
                ),
            )
        );

        $pageManager = $this
            ->getMockBuilder('\RedKiteCms\Content\Page\PageManager')
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $pageManager
            ->expects($this->at(0))
            ->method('contributor')
            ->with($username)
            ->will($this->returnSelf())
        ;

        $pageManager
            ->expects($this->at(1))
            ->method('edit')
            ->with($values['data']['pageName'], $values['data']['seoData'])
        ;

        $this->initApp($pageManager);

        $addPageAction = new EditSeoAction($this->app);
        $addPageAction->execute($values, $username);
    }

    protected function initApp($pageManager)
    {
        $this->app = $this
            ->getMockBuilder('\Silex\Application')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->app
            ->expects($this->at(0))
            ->method('offsetGet')
            ->with('red_kite_cms.page_manager')
            ->will($this->returnValue($pageManager));
        ;
    }
}
