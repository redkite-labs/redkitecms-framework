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

namespace RedKiteCms\Bridge\Assetic;

use RedKiteCms\TestCase;

/**
 * Class AsseticFactoryBuilderTest
 *
 * @author RedKite Labs <webmaster@redkite-labs.com>
 */
class AsseticFactoryBuilderTest extends TestCase
{
    private $configurationHandler;
    private $asseticFactoryBuilder = null;

    protected function setUp()
    {
        $this->configurationHandler = $this
            ->getMockBuilder('\RedKiteCms\Configuration\ConfigurationHandler')
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $this->asseticFactoryBuilder = new AsseticFactoryBuilder($this->configurationHandler);
    }

    /**
     * @dataProvider filtersProvider
     */
    public function testBuild(array $filters)
    {
        foreach($filters as $filterName => $filter) {
            $this->asseticFactoryBuilder->addFilter($filterName, $filter);
        }
        $factory = $this->asseticFactoryBuilder->build();
        $this->assertInstanceOf('\Assetic\Factory\AssetFactory', $factory);

        $assetManager = $factory->getAssetManager();
        $this->assertInstanceOf('\Assetic\AssetManager', $assetManager);
        $filterManager = $factory->getFilterManager();
        $this->assertInstanceOf('\Assetic\FilterManager', $filterManager);
        foreach($filters as $filterName => $filter) {
            $this->assertInstanceOf('\Assetic\Filter\FilterInterface', $filterManager->get($filterName));
        }
    }

    public function filtersProvider()
    {
        return array(
            array(
                array(),
            ),
            array(
                array(
                    'foo' => $this->getMock('\Assetic\Filter\FilterInterface'),
                ),
            ),
            array(
                array(
                    'foo' => $this->getMock('\Assetic\Filter\FilterInterface'),
                    'bar' => $this->getMock('\Assetic\Filter\FilterInterface'),
                ),
            ),
        );
    }
}
