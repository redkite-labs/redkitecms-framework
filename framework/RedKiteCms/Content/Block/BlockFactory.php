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

namespace RedKiteCms\Content\Block;

use RedKiteCms\Configuration\ConfigurationHandler;
use RedKiteCms\Exception\General\RuntimeException;
use Symfony\Component\Finder\Finder;

/**
 * This is the object implements the factory assigned to create blocks
 *
 * @author  RedKite Labs <webmaster@redkite-labs.com>
 * @package RedKiteCms\Content\Block
 */
class BlockFactory
{
    /**
     * @type array
     */
    private static $blocks = array();

    /**
     * Boots the factory
     *
     * @param \RedKiteCms\Configuration\ConfigurationHandler $configurationHandler
     */
    public static function boot(ConfigurationHandler $configurationHandler)
    {
        $pluginDirs = $configurationHandler->pluginFolders();
        foreach ($pluginDirs as $pluginDir) {
            self::$blocks += self::parse($pluginDir);
        }
    }

    /**
     * Returns the available blocks
     *
     * @return string
     */
    public static function getBlockClass($type)
    {
        if (!array_key_exists($type, self::$blocks)) {
            return '';
        }

        return self::$blocks[$type];
    }

    /**
     * Returns the available blocks
     *
     * @return array
     */
    public static function getAvailableBlocks()
    {
        return self::$blocks;
    }

    /**
     * {@inheritdoc}
     */
    public static function createBlock($type)
    {
        if (!array_key_exists($type, self::$blocks)) {
            throw new RuntimeException(
                sprintf('The plugin %s is not registered: the block has not been created', $type)
            );
        }

        $class = self::$blocks[$type];

        return self::instantiateBlock($class);
    }

    /**
     * {@inheritdoc}
     */
    public static function createAllBlocks()
    {
        $blocks = array();
        foreach (self::$blocks as $blockClass) {
            $blocks[] = self::instantiateBlock($blockClass);
        }

        return $blocks;
    }

    private static function instantiateBlock($class)
    {
        $reflectionClass = new \ReflectionClass($class);

        return $reflectionClass->newInstance();
    }

    private static function parse($pluginDir)
    {
        $blocks = array();
        $blocksDir = $pluginDir . '/Block';
        $finder = new Finder();
        $folders = $finder->directories()->depth(0)->in($blocksDir);
        foreach ($folders as $folder) {
            $blocks = array_merge($blocks, self::fetchBlocks($folder));
        }

        return $blocks;
    }

    private static function fetchBlocks($folder)
    {
        $blocks = array();
        $finder = new Finder();
        $files = $finder->files()->depth(0)->name('*Block.php')->in($folder . '/Core');
        $folderName = basename($folder);
        foreach ($files as $file) {
            $blockName = basename($file, 'Block.php');
            $blocks[$blockName] = sprintf('RedKiteCms\Block\%s\Core\%sBlock', $folderName, $blockName);
        }

        return $blocks;
    }
} 