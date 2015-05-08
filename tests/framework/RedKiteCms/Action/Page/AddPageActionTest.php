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

/**
 * Class AddPageActionTest
 *
 * @author RedKite Labs <webmaster@redkite-labs.com>
 */
class AddPageActionTest extends TestBaseAction
{
    private $theme;

    public function testAddPage()
    {
        $username = 'john';
        $values = array(
            'data' => array(
                'foo' => 'bar',
            )
        );
        $this->theme = $this
            ->getMockBuilder('\RedKiteCms\Content\Theme\Theme')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->boot($values, $username);

        $addPageAction = new AddPageAction($this->app);
        $addPageAction->execute($values, $username);
    }

    protected function initApp($pageManagerCollection)
    {
        parent::initApp($pageManagerCollection);

        $this->app
            ->expects($this->at(1))
            ->method('offsetGet')
            ->with('red_kite_cms.theme')
            ->will($this->returnValue($this->theme));
        ;
    }

    protected function configurePageManagerCollection($pageManagerCollection, $values, $username)
    {
        $pageManagerCollection
            ->expects($this->at(0))
            ->method('contributor')
            ->with($username)
            ->will($this->returnSelf())
        ;

        $pageManagerCollection
            ->expects($this->at(1))
            ->method('add')
            ->with($this->theme, $values['data'])
        ;
    }
}
