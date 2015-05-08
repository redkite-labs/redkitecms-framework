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
 * Class ArchiveBlockActionTest
 *
 * @author RedKite Labs <webmaster@redkite-labs.com>
 */
class ArchiveBlockActionTest extends TestBaseAction
{
    public function testArchiveBlock()
    {
        $username = 'john';
        $options = array("data" => array(
            'page' => 'index',
            'language' => 'en',
            'country' => 'GB',
            'slot' => 'logo',
            'name' => 'block2',
            'data' => array('foo' => 'bar'),
        ));

        $this->boot($options, $username, 'archive');

        $addBlockAction = new ArchiveBlockAction($this->app);
        $addBlockAction->execute($options, $username);
    }

    protected function initBlockManager($siteDir, $options, $username)
    {
        $data = $options["data"]["data"];
        unset($options["data"]["data"]);
        $options = $this->normalizeOptions($options);

        $blockManager = $this
            ->getMockBuilder('\RedKiteCms\Content\BlockManager\BlockManagerArchive')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $blockManager
            ->expects($this->once())
            ->method('archive')
            ->with($siteDir, $options, $username, $data)
        ;

        return $blockManager;
    }
}
