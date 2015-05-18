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
namespace RedKiteCms\Content\PageCollection;


use RedKiteCms\Bridge\Dispatcher\Dispatcher;
use RedKiteCms\Bridge\Monolog\DataLogger;
use RedKiteCms\Configuration\ConfigurationHandler;
use RedKiteCms\Content\BlockManager\BlockManagerApprover;
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
 * Class PageCollectionManager is the object assigned to manage a website page
 *
 * @author  RedKite Labs <webmaster@redkite-labs.com>
 * @package RedKiteCms\Content\Page
 */
class PageCollectionManager extends PageCollectionBase
{
    /**
     * @type \RedKiteCms\Content\SlotsManager\SlotsManagerFactory
     */
    private $slotsManagerFactory;

    /**
     * Constructor
     *
     * @param \RedKiteCms\Configuration\ConfigurationHandler $configurationHandler
     * @param \RedKiteCms\Content\SlotsManager\SlotsManagerFactory $slotsManagerFactory
     */
    public function __construct(ConfigurationHandler $configurationHandler, SlotsManagerFactoryInterface $slotsManagerFactory)
    {
        parent::__construct($configurationHandler);

        $this->slotsManagerFactory = $slotsManagerFactory;
    }

    /**
     * Adds a new page to the website using the given template from the given theme
     *
     * @param \RedKiteCms\Content\Theme\Theme $theme
     * @param $pageValues
     *
     * @return array Tha added page
     */
    public function add(Theme $theme, array $pageValues)
    {
        $pageName = $pageValues["name"];
        $pageDir = $this->pagesDir . '/' . $pageName;
        $this->pageExists($pageDir);

        // @codeCoverageIgnoreStart
        if (!@mkdir($pageDir)) {
            $this->folderNotCreated($pageDir);
        }
        // @codeCoverageIgnoreEnd

        $seoValues = $pageValues["seo"];
        unset($pageValues["seo"]);
        $encodedPage = json_encode($pageValues);
        $pageFile = $pageDir . '/' . $this->pageFile;
        $event = Dispatcher::dispatch(PageCollectionEvents::PAGE_COLLECTION_ADDING, new PageCollectionAddingEvent($pageFile, $encodedPage));
        $encodedPage = $event->getFileContent();
        FilesystemTools::writeFile($pageFile, $encodedPage);
        if ($this->pageFile != 'page.json') {
            FilesystemTools::writeFile($pageDir . '/page.json', $encodedPage);
        }

        foreach ($seoValues as $seoValue) {
            $languageName = $seoValue["language"];
            unset($seoValue["language"]);

            $languageDir = $pageDir . '/' . $languageName;
            @mkdir($languageDir);
            FilesystemTools::writeFile($languageDir . '/' . $this->seoFile, json_encode($seoValue));
            $theme->addTemplateSlots($pageValues["template"], $this->username);
        }

        Dispatcher::dispatch(PageCollectionEvents::PAGE_COLLECTION_ADDED, new PageCollectionAddedEvent($pageFile, $encodedPage));
        DataLogger::log(sprintf('Page "%s" was successfully added to the website', $pageName));

        return $pageValues;
    }

    /**
     * Edits the handled page
     * @param array $values
     *
     * @return string The encoded page
     */
    public function edit(array $values)
    {
        $currentName = $values["currentName"];
        unset($values["currentName"]);

        $pageDir = $this->pagesDir . '/' . $values["name"];
        if (!is_dir($pageDir)) {
            $pageDir = $this->pagesDir . '/' . $currentName;
        }

        $pageFile = $pageDir . '/' . $this->pageFile;
        $currentValues = json_decode(FilesystemTools::readFile($pageFile), true);
        if (array_key_exists("template", $values) && $currentValues["template"] != $values["template"]) {
            Dispatcher::dispatch(
                PageCollectionEvents::TEMPLATE_CHANGED,
                new TemplateChangedEvent($currentValues["template"], $values["template"], $this->username)
            );
        }
        $values = array_merge($currentValues, $values);

        $values = $this->slugifyPageName($values);
        $targetFolder = $this->pagesDir . '/' . $values["name"];
        $this->pageExists($targetFolder, $currentValues);

        $encodedPage = json_encode($values);
        $event = Dispatcher::dispatch(PageCollectionEvents::PAGE_COLLECTION_EDITING, new PageCollectionEditingEvent($pageFile, $encodedPage));
        $encodedPage = $event->getFileContent();

        FilesystemTools::writeFile($pageFile, $encodedPage);
        if ($currentName != $values["name"]) {
            rename($pageDir, $targetFolder);
        }

        Dispatcher::dispatch(PageCollectionEvents::PAGE_COLLECTION_EDITED, new PageCollectionEditedEvent($pageFile, $encodedPage));
        DataLogger::log(sprintf('Page "%s" was successfully edited', $currentName));

        return $encodedPage;
    }

    /**
     * Removes the given page
     *
     * @param $pageName
     */
    public function remove($pageName)
    {
        if ($pageName == $this->configurationHandler->homepage()) {
            throw new RuntimeException("exception_homepage_cannot_be_removed");
        }

        $pageDir = $this->pagesDir . '/' . $pageName;
        Dispatcher::dispatch(PageCollectionEvents::PAGE_COLLECTION_REMOVING, new PageCollectionRemovingEvent($this->username, $pageDir));

        $filesystem = new Filesystem();
        if (file_exists($pageDir . '/page.json')) {
            $filesystem->mirror($pageDir, $this->pagesRemovedDir . '/' . $pageName . "-" . date("Y-m-d-H.i.s"));
        }
        $filesystem->remove($pageDir);

        Dispatcher::dispatch(PageCollectionEvents::PAGE_COLLECTION_REMOVED, new PageCollectionRemovedEvent($this->username, $pageDir));
        DataLogger::log(sprintf('Page "%s" was successfully removed from website', $pageName));
    }

    private function pageExists($pageFolder, array $currentValues = null)
    {
        // Skips the control when the page name has not been changed
        $pageName = basename($pageFolder);
        if (is_dir($pageFolder)) {
            $exception = array(
                "message" => 'exception_page_exists',
                "parameters" => array(
                    "%page_name%" => $pageName,
                )
            );
            throw new InvalidArgumentException(json_encode($exception));
        }
    }

    /**
     * @codeCoverageIgnore
     */
    private function folderNotCreated($folder)
    {
        $exception = array(
            "message" => 'exception_cannot_create_folder',
            "parameters" => array(
                "%folder%" => $folder,
            )
        );
        throw new RuntimeException(json_encode($exception));
    }

    private function slugifyPageName(array $values)
    {
        $slugPageName = Utils::slugify($values["name"]);
        if ($slugPageName == $values["name"]) {
            return $values;
        }

        $event = Dispatcher::dispatch(
            PageCollectionEvents::SLUGGING_PAGE_COLLECTION_NAME,
            new SluggingPageNameEvent($values["name"], $slugPageName)
        );
        $sluggedText = $event->getChangedText();
        // @codeCoverageIgnoreStart
        if ($sluggedText != $slugPageName) {
            $slugPageName = $sluggedText;
        }
        // @codeCoverageIgnoreEnd
        $values["name"] = $slugPageName;

        return $values;
    }
} 