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

use RedKiteCms\Bridge\Dispatcher\Dispatcher;
use RedKiteCms\Bridge\Monolog\DataLogger;
use RedKiteCms\EventSystem\BlockEvents;
use RedKiteCms\EventSystem\Event\Block\BlockMovedAnotherSlotEvent;
use RedKiteCms\EventSystem\Event\Block\BlockMovedSameSlotEvent;
use RedKiteCms\EventSystem\Event\Block\BlockMovingAnotherSlotEvent;
use RedKiteCms\EventSystem\Event\Block\BlockMovingSameSlotEvent;
use RedKiteCms\Tools\FilesystemTools;
use RedKiteCms\Tools\JsonTools;
use Symfony\Component\Finder\Finder;

/**
 * Class BlockManagerMove is the object deputed to move a block both on the same slot or to another one
 *
 * @author  RedKite Labs <webmaster@redkite-labs.com>
 * @package RedKiteCms\Content\BlockManager
 */
class BlockManagerMove extends BlockManager
{
    /**
     * Moves the block
     *
     * @param string $sourceDir
     * @param array $options
     * @param string $username
     *
     * @return string The saved content
     */
    public function move($baseDir, array $options, $username)
    {
        $this->resolveMoveOptions($options);
        if (array_key_exists("targetSlot", $options)) {
            $options["slot"] = $options["targetSlot"];
            $block = $this->moveBlockToAnotherSlot($baseDir, $options, $username);

            return $block;
        }

        $options["slot"] = $options["sourceSlot"];
        $block = $this->moveBlockToSameSlot($baseDir, $options, $username);

        return $block;
    }

    /**
     * Defines the options required by the move method
     *
     * @param array $options
     */
    protected function resolveMoveOptions(array $options)
    {
        if ($this->optionsResolved) {
            // @codeCoverageIgnoreStart
            return;
            // @codeCoverageIgnoreEnd
        }

        $this->optionsResolver->clear();
        $this->optionsResolver->setRequired(
            array(
                'page',
                'language',
                'country',
                'sourceSlot',
                'position',
            )
        );

        $this->optionsResolver->setDefined(
            array(
                'targetSlot',
                'blockname',
                'oldName',
                'newName',
                'slot',
            )
        );

        $this->optionsResolver->resolve($options);
        $this->optionsResolved = true;
    }

    /**
     * Moves the block's archive dir to the new slot and adapts it according with the new block name
     *
     * @param string $archiveSourceFile
     * @param string $archiveTargetFile
     * @param string $blockName
     * @param string $slotName
     */
    private function moveArchiveDir($archiveSourceFile, $archiveTargetFile, $blockName, $slotName)
    {
        if (!is_dir($archiveSourceFile)) {
            return;
        }

        $this->filesystem->mirror($archiveSourceFile, $archiveTargetFile);
        $this->filesystem->remove($archiveSourceFile);

        $historyChanged = array();
        $historyFile = $archiveTargetFile . '/history.json';
        $history = json_decode(file_get_contents($historyFile), true);
        foreach($history as $key => $values) {
            $values["name"] = $blockName;
            $values["slot_name"] = $slotName;
            $historyChanged[$key] = $values;
        }
        file_put_contents($historyFile, json_encode($historyChanged));
    }

    /**
     * Changes the block and slot name when moving to another slot
     *
     * @param string $targetFile
     * @param string $blockName
     * @param string $slotName
     *
     * @return array The new block
     */
    private function changeBlockSlotAndName($targetFile, $blockName, $slotName)
    {
        $block = json_decode(FilesystemTools::readFile($targetFile), true);
        $block["name"] = $blockName;
        $block["slot_name"] = $slotName;
        $json = json_encode($block);
        FilesystemTools::writeFile($targetFile, $json);

        return $block;
    }

    private function moveBlockToAnotherSlot($baseDir, array $options, $username)
    {
        $sourceSlot = $options["sourceSlot"];
        $targetSlot = $options["targetSlot"];
        unset($options["sourceSlot"]);
        unset($options["targetSlot"]);

        $slotDirOptions = $options;
        $newName = $slotDirOptions["newName"];
        $slotDirOptions["blockname"] = $slotDirOptions["oldName"];
        unset($slotDirOptions["oldName"]);
        unset($slotDirOptions["newName"]);
        unset($slotDirOptions["position"]);
        $sourceDir = $this->fetchSlotDir($sourceSlot, $slotDirOptions, $baseDir, $username);
        $slotDirOptions["blockname"] = $newName;
        $targetDir = $this->fetchSlotDir($targetSlot, $slotDirOptions, $baseDir, $username);
        if (!is_dir($targetDir)) {
            $this->createContributorDir($baseDir, $options, $username);
        }

        Dispatcher::dispatch(
            BlockEvents::BLOCK_MOVING_ANOTHER_SLOT,
            new BlockMovingAnotherSlotEvent($this->serializer, $sourceSlot, $targetSlot)
        );

        $sourceFile = sprintf('%s/blocks/%s.json', $sourceDir, $options["oldName"]);
        $encodedBlock = $this->addSourceBlockToTargetSlot($sourceFile, $targetDir, $options);

        $archiveSourceFile = $sourceDir . '/archive/' . $options["oldName"];
        $archiveTargetFile = $targetDir . '/archive/' . $options["newName"];
        $this->moveArchiveDir($archiveSourceFile, $archiveTargetFile, $options["newName"], $options["slot"]);

        $options["blockname"] = $options["oldName"];
        $this->removeBlockFromSlotFile($options, $sourceDir);

        Dispatcher::dispatch(
            BlockEvents::BLOCK_MOVED_ANOTHER_SLOT,
            new BlockMovedAnotherSlotEvent($this->serializer, $sourceSlot, $targetSlot)
        );
        DataLogger::log(
            sprintf(
                'Block "%s" has been moved from the "%s" slot to the "%s" slot, on page "%s" for the "%s_%s" language. It has been renamed as "%s"',
                $options["oldName"],
                $sourceSlot,
                $targetSlot,
                $options["page"],
                $options["language"],
                $options["country"],
                $options["newName"]
            )
        );

        return $encodedBlock;
    }

    private function fetchSlotDir($slot, $options, $baseDir, $username)
    {
        $options["slot"] = $slot;

        return $this
            ->init($baseDir, $options, $username)
            ->getDirInUse()
        ;
    }

    private function addSourceBlockToTargetSlot($sourceFile, $targetDir, $options)
    {
        $targetBlockName = $options["blockname"] = $options["newName"];
        $this->addBlockToSlot($targetDir, $options);
        $targetFile = sprintf('%s/blocks/%s.json', $targetDir, $targetBlockName);

        $this->filesystem->copy($sourceFile, $targetFile, true);
        $this->filesystem->remove($sourceFile);

        $this->changeBlockSlotAndName($targetFile, $targetBlockName, $options["slot"]);
        $encodedBlock = FilesystemTools::readFile($targetFile);

        return $encodedBlock;
    }

    private function moveBlockToSameSlot($baseDir, array $options, $username)
    {
        $sourceDir = $this
            ->init($baseDir, $options, $username)
            ->getDirInUse();

        $slotsFilename = sprintf('%s/slot.json', $sourceDir);
        $slot = JsonTools::jsonDecode(FilesystemTools::readFile($slotsFilename), true);
        $blocks = $slot["blocks"];
        $key = array_search($options["blockname"], $blocks);
        $blockName = $blocks[$key];
        unset($blocks[$key]);
        array_splice($blocks, $options["position"], 0, $blockName);
        $slot["blocks"] = $blocks;
        $encodedSlot = json_encode($slot);

        $targetFile = sprintf('%s/blocks/%s.json', $sourceDir, $options["blockname"]);
        $event = Dispatcher::dispatch(
            BlockEvents::BLOCK_MOVING_SAME_SLOT,
            new BlockMovingSameSlotEvent($this->serializer, $blocks, $options["position"], $targetFile, $encodedSlot)
        );
        $slotContent = $event->getFileContent();

        FilesystemTools::writeFile($slotsFilename, $slotContent);
        $block = FilesystemTools::readFile($targetFile);

        Dispatcher::dispatch(
            BlockEvents::BLOCK_MOVED_SAME_SLOT,
            new BlockMovedSameSlotEvent($this->serializer, $blocks, $options["position"], $targetFile, $encodedSlot)
        );
        DataLogger::log(
            sprintf(
                'Block "%s" has been moved to position "%s" on the slot "%s" on "%s" page for "%s_%s" language',
                $options["blockname"],
                $options["position"],
                $options["slot"],
                $options["page"],
                $options["language"],
                $options["country"]
            )
        );

        return $block;
    }
}