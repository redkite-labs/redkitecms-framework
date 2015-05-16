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
use RedKiteCms\Content\BlockManager\BlockManagerAdd;
use RedKiteCms\TestCase;

class InvalidBlockTest extends BaseBlock
{
}

class BlockTest extends BaseBlock
{
    protected $value = "foo";
    protected $type = "MyLink";
    protected $customTag = "mylink";
    private $translatorOptions = array();

    public function __construct(array $translatorOptions)
    {
        $this->translatorOptions = $translatorOptions;

        parent::__construct();
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @inheritdoc
     */
    protected function getTranslatorOptions()
    {
        return $this->translatorOptions;
    }
}


/**
 * BaseBlockTest
 *
 * @author RedKite Labs <webmaster@redkite-labs.com>
 */
class BaseBlockTest extends TestCase
{
    /**
     * @expectedException \RedKiteCms\Exception\General\LogicException
     * @expectedExceptionMessage A derived class must always define the block type. Please define a protected property $type to set up the block type.
     */
    public function testTypePropertyNotDefined()
    {
        $block = new InvalidBlockTest();
        $block->getType();
    }

    /**
     * @expectedException \RedKiteCms\Exception\General\LogicException
     * @expectedExceptionMessage A derived class must always define the block custom tag property. Please define a protected property $customTag to set up the custom tag which will be used to render your block.
     */
    public function testCustomTagPropertyNotDefined()
    {
        $block = new InvalidBlockTest();
        $block->getCustomTag();
    }

    /**
     * @dataProvider blockProvider
     */
    public function testBlockCreated($translatorOptions)
    {
        $expectedTranslations = 0;
        if (count($translatorOptions) > 0) {
            $expectedTranslations = 1;
        }

        $params = (array_key_exists("params", $translatorOptions)) ? $translatorOptions["params"] : array();
        $domain = (array_key_exists("domain", $translatorOptions)) ? $translatorOptions["domain"] : 'RedKiteCms';

        $translator = $this->getMock('Symfony\Component\Translation\TranslatorInterface');
        $translator
            ->expects($this->exactly($expectedTranslations))
            ->method('trans')
            ->with('foo', $params, $domain, null)
        ;

        Translator::setTranslator($translator);

        $block = new BlockTest($translatorOptions);


        $this->assertEquals('MyLink', $block->getType());
        $this->assertEquals('mylink', $block->getCustomTag());

        $slotName = 'logo';
        $this->assertNull($block->getSlotName());
        $block->setSlotName($slotName);
        $this->assertEquals($slotName, $block->getSlotName());

        $name = 'link';
        $this->assertEmpty($block->getName());
        $block->setName($name);
        $this->assertEquals($name, $block->getName());

        $history = array('foo' => 'bar');
        $this->assertEmpty($block->getHistory());
        $block->setHistory($history);
        $this->assertEquals($history, $block->getHistory());

        $historyName = 'foo';
        $this->assertEmpty($block->getHistoryName());
        $block->setHistoryName($historyName);
        $this->assertEquals($historyName, $block->getHistoryName());
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
}