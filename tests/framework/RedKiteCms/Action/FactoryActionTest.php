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

namespace RedKiteCms\Action;

use RedKiteCms\TestCase;

/**
 * Class FactoryActionTest
 */
class FactoryActionTest extends TestCase
{
    private $factoryAction = null;
    private $app = null;

    protected function setUp()
    {
        $this->app = $this
            ->getMockBuilder('\Silex\Application')
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $this->factoryAction = new FactoryAction($this->app);
    }

    public function testCreateReturnsNullWhenActionClassDoesNotExists()
    {
        $action = $this->factoryAction->create('foo', 'bar');
        $this->assertNull($action);
    }

    public function testCreateReturnsActionObject()
    {
        $action = $this->factoryAction->create('block', 'add');
        $this->assertInstanceOf('\RedKiteCms\Action\Block\AddBlockAction', $action);
    }
}
