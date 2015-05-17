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
use RedKiteCms\Exception\General\InvalidArgumentException;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * The object assigned to handle a base elFinder connector
 *
 * @author  RedKite Labs <webmaster@redkite-labs.com>
 * @package RedKiteCms\Bridge\ElFinder
 */
abstract class ElFinderConnector implements ElFinderConnectorInterface
{
    /**
     * @type null|ConfigurationHandler
     */
    protected $configurationHandler = null;
    /**
     * @type array
     */
    protected $options = array();
    /**
     * @type bool
     */
    private $connectorLoaded = false;

    /**
     * Constructor
     */
    public function __construct(ConfigurationHandler $configurationHandler, OptionsResolver $optionsResolver = null)
    {
        $this->configurationHandler = $configurationHandler;
        if (null === $optionsResolver) {
            $optionsResolver = new OptionsResolver();
        }

        $optionsResolver->setRequired(
            array(
                'folder',
                'alias',
            )
        );

        $configuration = $this->configure();
        $optionsResolver->resolve($configuration);

        $this->options = $this->generateOptions($configuration["folder"], $configuration["alias"]);
    }

    /**
     * Starts the elFinder connector
     *
     * @codeCoverageIgnore
     */
    public function connect()
    {
        $this->loadConnectors();
        $connector = new \elFinderConnector(new \elFinder($this->options));
        $connector->run();
    }

    /**
     * Generates the elFinder options
     *
     * @param string $folder
     * @param string $rootAlias
     * @return array
     */
    private function generateOptions($folder, $rootAlias)
    {
        $assetsPath = $this->configurationHandler->uploadAssetsDir() . '/' . $folder;
        if (!is_dir($assetsPath)) {
            @mkdir($assetsPath);
        }

        $options = array(
            'locale' => '',
            'roots' => array(
                array(
                    'driver' => 'LocalFileSystem',
                    // driver for accessing file system (REQUIRED)
                    'path' => $assetsPath,
                    // path to files (REQUIRED)
                    'URL' => $this->configurationHandler->absoluteUploadAssetsDir() . '/' . $folder,
                    // URL to files (REQUIRED)
                    'accessControl' => 'access',
                    // disable and hide dot starting files (OPTIONAL)
                    'rootAlias' => $rootAlias
                    // disable and hide dot starting files (OPTIONAL)
                )
            )
        );

        return $options;
    }

    /**
     * @codeCoverageIgnore
     */
    private function loadConnectors()
    {
        if ($this->connectorLoaded) {
            return;
        }

        $webDir = $this->configurationHandler->webDir();
        require_once $webDir . '/components/redkitecms/elfinder/php/elFinderConnector.class.php';
        require_once $webDir . '/components/redkitecms/elfinder/php/elFinder.class.php';
        require_once $webDir . '/components/redkitecms/elfinder/php/elFinderVolumeDriver.class.php';
        require_once $webDir . '/components/redkitecms/elfinder/php/elFinderVolumeLocalFileSystem.class.php';

        $this->connectorLoaded = true;
    }
}
