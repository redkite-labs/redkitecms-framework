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

namespace RedKiteCms\Rendering\Controller\Deploy;

use RedKiteCms\Content\BlockManager\BlockManagerApprover;
use RedKiteCms\Rendering\Controller\BaseController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class SavePageController is the object assigned to save a page in production
 *
 * @author  RedKite Labs <webmaster@redkite-labs.com>
 * @package RedKiteCms\Rendering\Controller\Page
 */
abstract class BaseSavePageController extends BaseController
{
    protected function buldSitemap($options)
    {
        $options["sitemap_generator"]->writeSiteMap();
    }

    protected function removeCache($options)
    {
        $fs = new Filesystem();
        $fs->remove($options["configuration_handler"]->siteCacheDir());
    }

    /**
     * Configures the options for the resolver
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setRequired(
            array(
                'serializer',
                'sitemap_generator',
                'configuration_handler',
                'deployer',
            )
        );

        $resolver->setAllowedTypes(
            array(
                'serializer' => '\JMS\Serializer\Serializer',
                'sitemap_generator' => '\RedKiteCms\Content\SitemapGenerator\SitemapGenerator',
                'configuration_handler' => '\RedKiteCms\Configuration\ConfigurationHandler',
                'deployer' => '\RedKiteCms\Content\Content\Deploy\Deployer',
            )
        );
    }
}