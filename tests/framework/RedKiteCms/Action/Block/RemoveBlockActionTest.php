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
 * Class RemoveBlockActionTest
 *
 * @author RedKite Labs <webmaster@redkite-labs.com>
 */
class RemoveBlockActionTest extends TestBaseAction
{
    public function testRemoveBlock()
    {
        $username = 'john';
        $options = array("data" => array(
            'page' => 'index',
            'language' => 'en',
            'country' => 'GB',
            'slot' => 'logo',
            'name' => 'block2',
        ));

        $this->boot($options, $username, 'remove');

        $addBlockAction = new RemoveBlockAction($this->app);
        $addBlockAction->execute($options, $username);
    }

    protected function initBlockManager($siteDir, $options, $username)
    {
        $options = $this->normalizeOptions($options);

        $blockManager = $this
            ->getMockBuilder('\RedKiteCms\Content\BlockManager\BlockManagerRemove')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $blockManager
            ->expects($this->once())
            ->method('remove')
            ->with($siteDir, $options, $username)
        ;

        return $blockManager;
    }
}
