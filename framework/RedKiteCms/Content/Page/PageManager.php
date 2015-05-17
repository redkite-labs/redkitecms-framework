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

namespace RedKiteCms\Content\Page;

use RedKiteCms\Bridge\Dispatcher\Dispatcher;
use RedKiteCms\Bridge\Monolog\DataLogger;
use RedKiteCms\Content\PageCollection\PageCollectionBase;
use RedKiteCms\EventSystem\Event\Page\PermalinkChangedEvent;
use RedKiteCms\EventSystem\Event\Page\PageApprovedEvent;
use RedKiteCms\EventSystem\Event\Page\PageApprovingEvent;
use RedKiteCms\EventSystem\Event\Page\PageEditedEvent;
use RedKiteCms\EventSystem\Event\Page\PageEditingEvent;
use RedKiteCms\EventSystem\Event\Page\PageHidEvent;
use RedKiteCms\EventSystem\Event\Page\PageHidingEvent;
use RedKiteCms\EventSystem\Event\Page\PagePublishedEvent;
use RedKiteCms\EventSystem\Event\Page\PagePublishingEvent;
use RedKiteCms\EventSystem\Event\Page\SluggingPermalinkEvent;
use RedKiteCms\EventSystem\PageEvents;
use RedKiteCms\Exception\Publish\PageNotPublishedException;
use RedKiteCms\Tools\FilesystemTools;
use RedKiteCms\Tools\Utils;

/**
 * Class PageManager is the object assigned to handle the page's seo attributes
 *
 * @author  RedKite Labs <webmaster@redkite-labs.com>
 * @package RedKiteCms\Content\Seo
 */
class PageManager extends PageCollectionBase
{
    /**
     * @type array
     */
    private $changedPermalink = array();

    /**
     * Returns the changed permalink
     *
     * @return array
     */
    public function getChangedPermalink()
    {
        return $this->changedPermalink;
    }

    /**
     * Edits the seo attributes for the given page
     * @param string $pageName
     * @param array $values
     *
     * @return string The json seo content
     */
    public function edit($pageName, array $values)
    {
        $language = $values["language"];
        unset($values["language"]);
        $pageDir = $this->pagesDir . '/' . $pageName . '/' . $language;
        $seoFile = $pageDir . '/seo.json';
        if (null !== $this->username){
            $seoFile = $pageDir . '/' . $this->username . '.json';
        }
        $currentPage = json_decode(FilesystemTools::readFile($seoFile), true);

        $values = $this->slugifyPermalink($values);
        $this->dispatchPermalinkChanged($currentPage, $values);

        $encodedPage = json_encode($values);
        $event = Dispatcher::dispatch(PageEvents::PAGE_EDITING, new PageEditingEvent($seoFile, $encodedPage));
        $encodedPage = $event->getFileContent();
        FilesystemTools::writeFile($seoFile, $encodedPage);

        Dispatcher::dispatch(PageEvents::PAGE_EDITED, new PageEditedEvent($seoFile, $encodedPage));
        DataLogger::log(
            sprintf('Page SEO attributes "%s" for language "%s" were edited', $pageName, $language)
        );

        return $encodedPage;
    }

    /**
     * Approves the page in production
     * @param $pageName
     * @param $languageName
     *
     * @return string The json seo content
     */
    public function approve($pageName, $languageName)
    {
        $this->contributorDefined();

        $baseDir = $this->pagesDir . '/' . $pageName . '/' . $languageName;
        $sourceFile = $baseDir . '/' . $this->username . '.json';
        $targetFile = $baseDir . '/seo.json';
        if (!file_exists($targetFile)) {
            throw new PageNotPublishedException('exception_page_not_published');
        }

        $values = json_decode(FilesystemTools::readFile($sourceFile), true);
        if (!empty($values["current_permalink"])) {
            $values["changed_permalinks"][] = $values["current_permalink"];
            $values["current_permalink"] = "";
        }

        $encodedSeo = json_encode($values);
        $event = Dispatcher::dispatch(PageEvents::PAGE_APPROVING, new PageApprovingEvent($sourceFile, $encodedSeo));
        $encodedSeo = $event->getFileContent();

        FilesystemTools::writeFile($sourceFile, $encodedSeo);
        FilesystemTools::writeFile($targetFile, $encodedSeo);

        Dispatcher::dispatch(PageEvents::PAGE_APPROVED, new PageApprovedEvent($sourceFile, $encodedSeo));
        DataLogger::log(sprintf('Page SEO attributes "%s" for language "%s" were approved', $pageName, $languageName));

        return $encodedSeo;
    }

    /**
     * Publish the current seo
     * @param $pageName
     * @param $languageName
     */
    public function publish($pageName, $languageName)
    {
        $this->contributorDefined();

        $baseDir = $this->pagesDir . '/' . $pageName;
        $pageCollectionSourceFile = $baseDir . '/' . $this->username . '.json';
        $pageCollectionTargetFile = $baseDir . '/page.json';
        $pageDir = $baseDir . '/' . $languageName;
        $pageSourceFile = $pageDir . '/' . $this->username . '.json';
        $pageTargetFile = $pageDir . '/seo.json';

        Dispatcher::dispatch(PageEvents::PAGE_PUBLISHING, new PagePublishingEvent());
        copy($pageCollectionSourceFile, $pageCollectionTargetFile);
        copy($pageSourceFile, $pageTargetFile);

        Dispatcher::dispatch(PageEvents::PAGE_PUBLISHED, new PagePublishedEvent());
        DataLogger::log(sprintf('Page "%s" for language "%s" was published in production', $pageName, $languageName));
    }

    /**
     * Hides the current seo
     * @param $pageName
     * @param $languageName
     */
    public function hide($pageName, $languageName)
    {
        $this->contributorDefined();

        $baseDir = $this->pagesDir . '/' . $pageName . '/' . $languageName;
        $sourceFile = $baseDir . '/seo.json';

        Dispatcher::dispatch(PageEvents::PAGE_HIDING, new PageHidingEvent());
        unlink($sourceFile);

        Dispatcher::dispatch(PageEvents::PAGE_HID, new PageHidEvent());
        DataLogger::log(sprintf('Page "%s" for language "%s" was hidden from production', $pageName, $languageName));
    }

    private function slugifyPermalink(array $values)
    {
        $sluggedPermalink = Utils::slugify($values["permalink"]);
        if ($sluggedPermalink == $values["permalink"]) {
            return $values;
        }

        $event = Dispatcher::dispatch(
            PageEvents::SLUGGING_PERMALINK,
            new SluggingPermalinkEvent($values["permalink"], $sluggedPermalink)
        );
        $sluggedText = $event->getChangedText();
        // @codeCoverageIgnoreStart
        if ($sluggedText != $sluggedPermalink) {
            $sluggedPermalink = $sluggedText;
        }
        // @codeCoverageIgnoreEnd
        $values["permalink"] = $sluggedPermalink;

        return $values;
    }

    private function dispatchPermalinkChanged(array $currentPage, array $values)
    {
        if ($currentPage["permalink"] == $values["permalink"]) {
            return;
        }

        if (!array_key_exists("current_permalink", $values)) {
            $values["current_permalink"] = $currentPage["permalink"];
        }

        $this->changedPermalink = array(
            'old' => $currentPage["permalink"],
            'new' => $values["permalink"],
        );
        Dispatcher::dispatch(
            PageEvents::PERMALINK_CHANGED,
            new PermalinkChangedEvent($currentPage["permalink"], $values["permalink"])
        );
    }
} 