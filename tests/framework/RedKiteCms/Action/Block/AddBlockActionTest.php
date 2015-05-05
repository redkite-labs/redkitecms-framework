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
 * Class FactoryActionTest
 */
class AddBlockActionTest extends TestCase
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
        $this->factoryAction = $this
            ->getMockBuilder('\RedKiteCms\Action\FactoryAction')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->configurationHandler = $this
            ->getMockBuilder('\RedKiteCms\Configuration\ConfigurationHandler\ConfigurationHandler')
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $this->addBlockAction = new AddBlockAction($this->app);

    }

    public function testAddBlock()
    {
        $username = 'john';
        $options = array();

        $this->addBlockAction->execute($options, $username);
        //$this->assertNull($action);
    }

    protected function initApp()
    {
        /*
        $this->app
            ->expects($this->at(0))
            ->method('setName')
            ->with($blockAddedName)
        ;*/
    }
}
