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

namespace RedKiteCms\Content\SitemapGenerator;

use RedKiteCms\Configuration\ConfigurationHandler;
use RedKiteCms\Content\PageCollection\PagesCollectionParser;


/**
 * SitemapGenerator is the object assigned to generate and write the website sitemap
 *
 * @author RedKite Labs <webmaster@redkite-labs.com>
 */
class SitemapGenerator implements SitemapGeneratorInterface
{
    /** @var ConfigurationHandler  */
    private $configurationHandler;
    /** @var PagesCollectionParser */
    private $pagesCollectionParser;
    /** @var \Twig_Environment */
    private $twig;

    /**
     * Constructor
     * 
     * @param PagesCollectionParser $pagesCollectionParser
     * @param \Twig_Environment $twig
     */
    public function __construct(ConfigurationHandler $configurationHandler, PagesCollectionParser $pagesCollectionParser, \Twig_Environment $twig)
    {
        $this->configurationHandler = $configurationHandler;
        $this->pagesCollectionParser = $pagesCollectionParser;
        $this->twig = $twig;
    }

    /**
     * {@inheritdoc}
     */
    public function writeSiteMap()
    {
        $sitemap = $this->generateSiteMap();

        return @file_put_contents($this->configurationHandler->webDir() . '/sitemap.xml', $sitemap);
    }

    /**
     * Generated the site map
     *
     * @param  string $websiteUrl
     * @return string
     */
    protected function generateSiteMap()
    {
        $urls = array();
        $siteName = $this->configurationHandler->siteName();
        foreach ($this->pagesCollectionParser->pages() as $page) {
            foreach($page["seo"] as $seo) {
                $urls[] = array(
                    'href' => $siteName . '/' . $seo["permalink"],
                    'frequency' => $seo["sitemap_frequency"],
                    'priority' => $seo["sitemap_priority"],
                );
            }
        }

        return $this->twig->render('RedKiteCms/Resources/views/Sitemap/sitemap.html.twig', array('urls' => $urls));
    }
}
