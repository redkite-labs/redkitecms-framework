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
namespace RedKiteCms\Content\Deploy;


use RedKiteCms\Bridge\Dispatcher\Dispatcher;
use RedKiteCms\Bridge\Monolog\DataLogger;
use RedKiteCms\Configuration\ConfigurationHandler;
use RedKiteCms\Content\BlockManager\BlockManagerApprover;
use RedKiteCms\Content\PageCollection\PageCollectionBase;
use RedKiteCms\Content\SlotsManager\SlotsManagerFactoryInterface;
use RedKiteCms\Content\Theme\Theme;
use RedKiteCms\EventSystem\Event\Page\PageSavedEvent;
use RedKiteCms\EventSystem\Event\PageCollection\PageCollectionAddedEvent;
use RedKiteCms\EventSystem\Event\PageCollection\PageCollectionAddingEvent;
use RedKiteCms\EventSystem\Event\PageCollection\PageCollectionEditedEvent;
use RedKiteCms\EventSystem\Event\PageCollection\PageCollectionEditingEvent;
use RedKiteCms\EventSystem\Event\PageCollection\PageCollectionRemovedEvent;
use RedKiteCms\EventSystem\Event\PageCollection\PageCollectionRemovingEvent;
use RedKiteCms\EventSystem\Event\PageCollection\SiteSavedEvent;
use RedKiteCms\EventSystem\Event\PageCollection\SluggingPageNameEvent;
use RedKiteCms\EventSystem\Event\PageCollection\TemplateChangedEvent;
use RedKiteCms\EventSystem\PageCollectionEvents;
use RedKiteCms\EventSystem\PageEvents;
use RedKiteCms\Exception\General\InvalidArgumentException;
use RedKiteCms\Exception\General\RuntimeException;
use RedKiteCms\Tools\FilesystemTools;
use RedKiteCms\Tools\Utils;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

/**
 * Class Deployer is the object assigned to deploy the website
 *
 * @author  RedKite Labs <webmaster@redkite-labs.com>
 * @package RedKiteCms\Content\Page
 */
class Deployer extends PageCollectionBase
{
    /**
     * Saves the handled page
     *
     * @param \RedKiteCms\Content\BlockManager\BlockManagerApprover $approver
     * @param array $options
     * @param bool $saveCommonSlots Saves the common slots when true
     */
    public function save(BlockManagerApprover $approver, array $options, $saveCommonSlots = true)
    {
        $this->contributorDefined();

        $filesystem = new Filesystem();
        $pageDir = $this->pagesDir . '/' . $options["page"];
        $filesystem->copy($pageDir . '/' . $this->pageFile, $pageDir . '/page.json', true);

        $pageDir .= '/' . $options["language"] . '_' . $options["country"];
        if ($this->seoFile != "seo.json") {

            $sourceFile = $pageDir . '/' . $this->seoFile;
            $values = json_decode(file_get_contents($sourceFile), true);
            if (array_key_exists("current_permalink", $values)) {
                $values["changed_permalinks"][] = $values["current_permalink"];
                unset($values["current_permalink"]);
                file_put_contents($sourceFile, json_encode($values));
            }

            $filesystem->copy($sourceFile, $pageDir . '/seo.json', true);
        }
        $approvedBlocks = $this->saveBlocks($approver, $pageDir, $options);

        if ($saveCommonSlots) {
            $slotsDir = $this->baseDir . '/slots';
            $approvedCommonBlocks = $this->saveBlocks($approver, $slotsDir, $options);
            $approvedBlocks = array_merge($approvedBlocks, $approvedCommonBlocks);
        }

        Dispatcher::dispatch(PageEvents::PAGE_SAVED, new PageSavedEvent($pageDir, null, $approvedBlocks));
        DataLogger::log(sprintf('Page "%s" was successfully saved in production', $options["page"]));
    }

    /**
     * Save the all website pages
     *
     * @param \RedKiteCms\Content\BlockManager\BlockManagerApprover $approver
     * @param array $languages
     * @param bool $saveCommonSlots Saves the common slots when true
     */
    public function saveAllPages(BlockManagerApprover $approver, array $languages, $saveCommonSlots = true)
    {
        $this->contributorDefined();

        $finder = new Finder();
        $pages = $finder->directories()->depth(0)->in($this->pagesDir);
        foreach ($pages as $page) {
            $page = (string)$page;
            $pageName = basename($page);
            foreach ($languages as $language) {
                $tokens = explode("_", $language);
                $options = array(
                    'page' => $pageName,
                    'language' => $tokens[0],
                    'country' => $tokens[1],
                );
                $this->save($approver, $options, $saveCommonSlots);
            }
            $saveCommonSlots = false;
        }

        Dispatcher::dispatch(PageCollectionEvents::SITE_SAVED, new SiteSavedEvent());
        DataLogger::log('The whole website\'s pages were successfully saved in production');
    }

    private function saveBlocks(BlockManagerApprover $approver, $sourcePath, array $options)
    {
        $approvedBlocks = array();
        $finder = new Finder();
        $slots = $finder->directories()->depth(0)->in($sourcePath);
        foreach ($slots as $slot) {
            $basePath = (string)$slot;
            $approvedBlocks[] = $this->doSaveBlocks($approver, $basePath, $options);
        }

        return $approvedBlocks;
    }

    private function doSaveBlocks(BlockManagerApprover $approver, $basePath, array $options)
    {
        $options["slot"] = basename($basePath);
        $slotPath = sprintf('%s/contributors/%s', FilesystemTools::slotDir($this->baseDir, $options), $this->username);
        if (!is_dir($slotPath)) {
            return array();
        }

        $activeSlotDefinition = json_decode(FilesystemTools::readFile($basePath . '/active/slot.json'), true);
        $contributorSlotDefinition = json_decode(FilesystemTools::readFile($slotPath . '/slot.json'), true);

        $removedBlocks = array();
        $contributorSlotDefinitionBlocks = $contributorSlotDefinition["blocks"];
        if (null === $contributorSlotDefinitionBlocks) {
            $contributorSlotDefinitionBlocks = array();
        }
        if (null !== $activeSlotDefinition) {
            $removedBlocks = array_diff_key($activeSlotDefinition["blocks"], $contributorSlotDefinitionBlocks);
        }

        foreach ($removedBlocks as $blockName) {
            $options["blockname"] = $blockName;
            $approver->approveRemoval($this->baseDir, $options, $this->username);
        }

        $approvedBlocks = array();
        $blocks = array_diff_key($contributorSlotDefinitionBlocks, $removedBlocks);
        foreach ($blocks as $blockName) {
            $options["blockname"] = $blockName;
            $approved = $approver->approve($this->baseDir, $options, $this->username);
            $approvedBlocks[] = $approved;
        }

        return $approvedBlocks;
    }
} 