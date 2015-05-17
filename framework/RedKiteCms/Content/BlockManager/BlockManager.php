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

namespace RedKiteCms\Content\BlockManager;

use JMS\Serializer\SerializerInterface;
use RedKiteCms\Content\Block\BlockFactoryInterface;
use RedKiteCms\FilesystemEntity\FilesystemEntity;
use RedKiteCms\Tools\FilesystemTools;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\OptionsResolver\OptionsResolver;


/**
 * Class BlockManager is the base object assigned to handle a block
 *
 * @author  RedKite Labs <webmaster@redkite-labs.com>
 * @package RedKiteCms\Content\BlockManager
 */
abstract class BlockManager extends FilesystemEntity
{
    /**
     * @type bool
     */
    protected $optionsResolved = false;
    /**
     * @type \RedKiteCms\Content\Block\BlockFactoryInterface
     */
    protected $blockFactory;

    /**
     * @param \JMS\Serializer\SerializerInterface                $serializer
     * @param \RedKiteCms\Content\Block\BlockFactoryInterface    $blockFactory
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function __construct(SerializerInterface $serializer, BlockFactoryInterface $blockFactory, OptionsResolver $resolver)
    {
        parent::__construct($serializer, $resolver);

        $this->blockFactory = $blockFactory;
        $this->filesystem = new Filesystem();
    }

    /**
     * Defines the common required options by a block manager
     *
     * @param array $options
     */
    protected function resolveOptions(array $options)
    {
        if ($this->optionsResolved) {
            return;
        }

        $this->optionsResolver->clear();
        $this->optionsResolver->setRequired(
            array(
                'blockname',
            )
        );

        parent::resolveOptions($options);
        $this->optionsResolved = true;
    }

    /**
     * Creates the contributor folder
     *
     * @param string $sourceDir
     * @param array $options
     * @param string $username
     */
    protected function createContributorDir($sourceDir, array $options, $username)
    {
        if (null === $username) {
            return;
        }

        $this->init($sourceDir, $options, $username);
        if (is_dir($this->contributorDir)) {
            return;
        }

        $this->filesystem->copy($this->productionDir . '/slot.json', $this->contributorDir . '/slot.json', true);
        $this->filesystem->mirror($this->productionDir . '/blocks', $this->contributorDir . '/blocks');
    }

    /**
     * Adds a default block to the given slot
     *
     * @param string $dir
     * @param array $options
     *
     * @return string
     */
    protected function addBlockToSlot($dir, array $options)
    {
        $slot = $this->getSlotDefinition($dir);
        $blocks = $slot["blocks"];
        $blockName = $options["blockname"];
        $position = $options["position"];
        array_splice($blocks, $position, 0, $blockName);

        $slot["next"] = str_replace('block', '', $blockName) + 1;
        $slot["blocks"] = $blocks;

        $this->saveSlotDefinition($dir, $slot);

        return $blockName;
    }

    /**
     * Fetches the slot definition
     *
     * @param string $dir
     *
     * @return array
     */
    protected function getSlotDefinition($dir)
    {
        $slotsFilename = $this->getSlotDefinitionFile($dir);

        return json_decode(FilesystemTools::readFile($slotsFilename), true);
    }

    /**
     * Gets the slot file
     *
     * @param string $dir
     *
     * @return string
     */
    protected function getSlotDefinitionFile($dir)
    {
        return sprintf('%s/slot.json', $dir);
    }

    /**
     * Saves the slot definition
     *
     * @param string $dir
     * @param array $slot
     */
    protected function saveSlotDefinition($dir, array $slot)
    {
        $slotsFilename = $this->getSlotDefinitionFile($dir);

        FilesystemTools::writeFile($slotsFilename, json_encode($slot), $this->filesystem);
    }

    /**
     * Removes a block from the slot files and returns back the block name
     *
     * @param array $options
     * @param string $targetDir
     *
     * @return string
     */
    protected function removeBlockFromSlotFile(array $options, $targetDir = null)
    {
        $targetDir = $this->workDirectory($targetDir);

        $slot = $this->getSlotDefinition($targetDir);
        $blockName = $options["blockname"];

        $tmp = array_flip($slot["blocks"]);
        unset($tmp[$blockName]);
        $slot["blocks"] = array_keys($tmp);

        $this->saveSlotDefinition($targetDir, $slot);

        return $blockName;
    }
}