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

namespace RedKiteCms\Content\BlockManager;

use org\bovigo\vfs\vfsStream;
use RedKiteCms\Bridge\Translation\Translator;
use RedKiteCms\Content\Block\BaseBlock;
use RedKiteCms\Content\Block\BlockFactory;
use RedKiteCms\Content\Block\ExtendableBlock;
use RedKiteCms\Content\Block\ExtendableCollectionBlock;
use RedKiteCms\Content\BlockManager\BlockManagerAdd;
use RedKiteCms\TestCase;


class TestExtendableCollectionBlock extends ExtendableCollectionBlock
{
}


/**
 * ExtendableBlockTest
 *
 * @author RedKite Labs <webmaster@redkite-labs.com>
 */
class ExtendableCollectionBlockTest extends TestCase
{
    public function testBlockCreated()
    {
        $children = array(
            $this->initChild(),
            $this->initChild(),
        );
        $tags = array(
            "class" => "bar",
        );
        $block = new TestExtendableCollectionBlock($children, $tags);
        $expectedSource = "children:\n";
        $expectedSource .= "  item1:\n";
        $expectedSource .= "    value: foo\n";
        $expectedSource .= "    tags:\n";
        $expectedSource .= "      class: bar\n";
        $expectedSource .= "    type: Link\n";
        $expectedSource .= "  item2:\n";
        $expectedSource .= "    value: foo\n";
        $expectedSource .= "    tags:\n";
        $expectedSource .= "      class: bar\n";
        $expectedSource .= "    type: Link\n";
        $expectedSource .= "tags:\n";
        $expectedSource .= "  class: bar\n";

        $this->assertEquals($expectedSource, $block->getSource());

        $children = array($this->getMock('\RedKiteCms\Block\Link\Core\LinkBlock'));
        $block->setChildren($children);
        $this->assertEquals($children, $block->getChildren());
    }

    private function initChild()
    {
        $link = $this->getMock('\RedKiteCms\Block\Link\Core\LinkBlock');
        $source = "value: foo\n";
        $source .= "tags:\n";
        $source .= "  class: bar\n";

        $link->expects($this->once())
            ->method('getSource')
            ->will($this->returnValue($source))
        ;

        $link->expects($this->once())
            ->method('getType')
            ->will($this->returnValue('Link'))
        ;

        return $link;
    }
}