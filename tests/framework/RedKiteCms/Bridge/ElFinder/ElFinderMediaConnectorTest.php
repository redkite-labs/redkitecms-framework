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

/**
 * Class ElFinderMediaConnectorTest
 *
 * @author RedKite Labs <webmaster@redkite-labs.com>
 */
class ElFinderMediaConnectorTest extends TestCase
{
    public function testOptions()
    {
        $expectedOptions = array(
            "folder" => 'media',
            "alias" => 'Media',
        );

        $configurationHandler = $this
            ->getMockBuilder('\RedKiteCms\Configuration\ConfigurationHandler')
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $optionsResolver = $this->getMock('\Symfony\Component\OptionsResolver\OptionsResolver');

        $connector = new ElFinderMediaConnector($configurationHandler, $optionsResolver);
        $this->assertEquals($expectedOptions, $connector->configure());
    }
}
