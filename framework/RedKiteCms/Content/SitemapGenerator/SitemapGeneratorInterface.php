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

/**
 * Represents a class to define an object able to write a site map for a website
 *
 * @author RedKite Labs <webmaster@redkite-labs.com>
 */
interface SitemapGeneratorInterface
{
    /**
     * Writes the sitemap inside the given path for the provided website url
     */
    public function writeSiteMap();
}
