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

namespace RedKiteCms\Bridge\ElFinder;

use RedKiteCms\Configuration\ConfigurationHandler;
use RedKiteCms\TestCase;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ElFinderConnectorTester extends ElFinderConnector
{
    private $configurationOptions;

    public function __construct(ConfigurationHandler $configurationHandler, OptionsResolver $optionsResolver = null, $options = array())
    {
        $this->configurationOptions = $options;

        parent::__construct($configurationHandler, $optionsResolver);
    }

    public function configure()
    {
        return $this->configurationOptions;
    }

    public function getOptions()
    {
        return $this->options;
    }
}

/**
 * Class ElFinderConnectorTest
 *
 * @author RedKite Labs <webmaster@redkite-labs.com>
 */
class ElFinderConnectorTest extends TestCase
{
    private $configurationHandler;

    protected function setUp()
    {
        $this->configurationHandler = $this
            ->getMockBuilder('\RedKiteCms\Configuration\ConfigurationHandler')
            ->setMethods(array('uploadAssetsDir', 'absoluteUploadAssetsDir'))
            ->disableOriginalConstructor()
            ->getMock()
        ;
    }

    /**
     * @expectedException \Symfony\Component\OptionsResolver\Exception\MissingOptionsException
     * @expectedExceptionMessage The required options "alias", "folder" are missing.
     */
    public function testEmptyOptions()
    {
        $connector = new ElFinderConnectorTester($this->configurationHandler);
    }

    /**
     * @expectedException \Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException
     * @expectedExceptionMessage The option "foo" does not exist. Known options are: "alias", "folder".
     */
    public function testUnknownOption()
    {
        $connector = new ElFinderConnectorTester($this->configurationHandler, new OptionsResolver(), array('foo' => 'bar'));
    }

    public function testOptionsAreGenerated()
    {
        $options = array(
            "folder" => 'media',
            "alias" => 'Media',
        );

        $expectedOptions = array(
            'locale' => '',
            'roots' => array(
                array(
                    'driver' => 'LocalFileSystem',
                    'path' => '/path/to/assets/media',
                    'URL' => '/absolute/path/to/assets/media',
                    'accessControl' => 'access',
                    'rootAlias' => 'Media',
                ),
            )
        );

        $this->configurationHandler
            ->expects($this->once())
            ->method('uploadAssetsDir')
            ->will($this->returnValue('/path/to/assets'))
        ;

        $this->configurationHandler
            ->expects($this->once())
            ->method('absoluteUploadAssetsDir')
            ->will($this->returnValue('/absolute/path/to/assets'))
        ;

        $optionsResolver = $this->getMock('\Symfony\Component\OptionsResolver\OptionsResolver');
        $optionsResolver
            ->expects($this->once())
            ->method('resolve')
            ->with($options)
        ;

        $connector = new ElFinderConnectorTester($this->configurationHandler, $optionsResolver, $options);
        $this->assertEquals($expectedOptions, $connector->getOptions());
    }
}
