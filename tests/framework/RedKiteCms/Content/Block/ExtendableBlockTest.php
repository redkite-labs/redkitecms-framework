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
use RedKiteCms\Content\BlockManager\BlockManagerAdd;
use RedKiteCms\TestCase;


class TestExtendableBlock extends ExtendableBlock
{
}


/**
 * ExtendableBlockTest
 *
 * @author RedKite Labs <webmaster@redkite-labs.com>
 */
class ExtendableBlockTest extends TestCase
{
    public function testBlockCreated()
    {
        $translator = $this->getMock('Symfony\Component\Translation\TranslatorInterface');
        $translator
            ->expects($this->once())
            ->method('trans')
            ->with('', array(), 'RedKiteCms', null)
        ;

        Translator::setTranslator($translator);

        $value = "foo";
        $tags = array(
            "class" => "bar",
        );
        $block = new TestExtendableBlock($value, $tags);
        $expectedSource = "value: foo\n";
        $expectedSource .= "tags:\n";
        $expectedSource .= "  class: bar\n";
        $this->assertEquals($expectedSource, $block->getSource());
        $this->assertEquals($tags, $block->getTags());
        $tags = array(
            "class" => "baz",
        );
        $block->setTags($tags);
        $this->assertEquals($tags, $block->getTags());

        $source = 'foo: bar';
        $block->setSource($source);
        $this->assertEquals($source, $block->getSource());
    }

    public function blockProvider()
    {
        return array(
            array(
                array(),
            ),
            array(
                array(
                    "fields" => array('value'),
                ),
            ),
            array(
                array(
                    "fields" => array('value'),
                    "params" => array('foo'),
                    "domain" => "CustomDomain",
                ),
            ),
        );
    }
/*
    public function testBlockTranslated()
    {
        $translator = $this->getMock('Symfony\Component\Translation\TranslatorInterface');
        $translator
            ->expects($this->once())
            ->method('trans')
            ->with('foo', array(), 'RedKiteCms', null)
        ;

        Translator::setTranslator($translator);

        $block = new BlockWithTranslationsTest();
        $block->setSlotName('logo');
        $block->setHistory(array('foo' => 'bar'));
    }*/
}