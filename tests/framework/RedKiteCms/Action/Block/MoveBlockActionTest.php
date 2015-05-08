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

/**
 * Class MoveBlockActionTest
 *
 * @author RedKite Labs <webmaster@redkite-labs.com>
 */
class MoveBlockActionTest extends TestBaseAction
{
    /**
     * @dataProvider optionsProvider
     */
    public function testMoveBlock(array $options)
    {
        $username = 'john';

        $this->boot($options, $username, 'move');

        $addBlockAction = new MoveBlockAction($this->app);
        $addBlockAction->execute($options, $username);
    }

    public function optionsProvider()
    {
        return array(
            array(
                array("data" => array(
                    'page' => 'index',
                    'language' => 'en',
                    'country' => 'GB',
                    'sourceSlot' => 'logo',
                    'oldName' => 'block1',
                    'newName' => 'block2',
                    'name' => 'block2',
                    'position' => 1,
                ))
            ),
            array(
                array("data" => array(
                    'page' => 'index',
                    'language' => 'en',
                    'country' => 'GB',
                    'sourceSlot' => 'logo',
                    'targetSlot' => 'menu',
                    'oldName' => 'block1',
                    'newName' => 'block2',
                    'name' => 'block1',
                    'position' => 2,
                ))
            ),
        );
    }

    protected function initBlockManager($siteDir, $options, $username)
    {
        $options = $this->normalizeOptions($options);

        $blockManager = $this
            ->getMockBuilder('\RedKiteCms\Content\BlockManager\BlockManagerMove')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $blockManager
            ->expects($this->once())
            ->method('move')
            ->with($siteDir, $options, $username)
        ;

        return $blockManager;
    }
}
