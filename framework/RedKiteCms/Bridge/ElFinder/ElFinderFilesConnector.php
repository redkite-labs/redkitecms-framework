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

/**
 * The object assigned to defined an ElFinder connector to manage any kind of file
 *
 * @author  RedKite Labs <webmaster@redkite-labs.com>
 * @package RedKiteCms\Bridge\ElFinder
 */
class ElFinderFilesConnector extends ElFinderConnector
{
    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        return array(
            "folder" => 'files',
            "alias" => 'Files',
        );
    }
}
