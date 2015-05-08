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

namespace RedKiteCms\BlockManager;

use RedKiteCms\Content\BlockManager\BlockManagerFactory;

/**
 * Class BlockManagerFactoryTest
 *
 * @author  RedKite Labs <webmaster@redkite-labs.com>
 * @package RedKiteCms\Content\BlockManager
 */
class BlockManagerFactoryTest extends BlockManagerBaseTestCase
{
    private $blockManagerFactory = null;

    protected function setUp()
    {
        parent::setUp();

        $this->blockManagerFactory = new BlockManagerFactory($this->serializer, $this->blockFactory, $this->optionsResolver);
    }

    public function testCreateReturnsNullWhenBlockManagerClassDoesNotExists()
    {
        $action = $this->blockManagerFactory->create('foo');
        $this->assertNull($action);
    }

    public function testCreateReturnsBlockManagerObject()
    {
        $blockManager = $this->blockManagerFactory->create('add');
        $this->assertInstanceOf('\RedKiteCms\Content\BlockManager\BlockManagerAdd', $blockManager);
    }
}
