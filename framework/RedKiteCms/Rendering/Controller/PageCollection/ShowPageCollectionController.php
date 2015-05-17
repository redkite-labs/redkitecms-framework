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
namespace RedKiteCms\Rendering\Controller\PageCollection;

use RedKiteCms\Configuration\ConfigurationHandler;
use RedKiteCms\Core\RedKiteCms\Core\Form\PageCollection\PageType;
use RedKiteCms\Core\RedKiteCms\Core\Form\PageCollection\SeoType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ShowPageCollectionController is the object assigned to show the page collection dashboard interface
 *
 * @author  RedKite Labs <webmaster@redkite-labs.com>
 * @package RedKiteCms\Rendering\Controller\Page
 */
abstract class ShowPageCollectionController extends BasePageCollectionController
{
    /**
     * Implements the action to show the page collection dashboard interface
     * @param array $options
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function show(array $options)
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);
        $this->options = $resolver->resolve($options);

        $pagesParser = $this->options["pages_collection_parser"];
        $pages = $pagesParser
            ->contributor($this->options["username"])
            ->parse()
            ->pages()
        ;

        $this->options["template_assets"]->boot('dashboard');
        $templates = $this->options["theme"]->templates();
        $formFactory = $this->options['form_factory'];
        $form = $formFactory->create(new PageType(array_combine($templates, $templates)));
        $pageForm = $form->createView();
        $form = $formFactory->create(new SeoType());
        $seoForm = $form->createView();

        $template = 'RedKiteCms/Resources/views/Dashboard/pages.html.twig';
        $languages = $this->options["configuration_handler"]->languages();

        return $options["twig"]->render(
            $template,
            array(
                "template_assets_manager" => $this->options["template_assets"],
                "pages" => rawurlencode(json_encode($pages)),
                "pageForm" => $pageForm,
                "seoForm" => $seoForm,
                "version" => ConfigurationHandler::getVersion(),
                "home_template" => $this->options["theme"]->homepageTemplate(),
                "languages" => $languages,
            )
        );
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
                'configuration_handler',
                'pages_collection_parser',
                'theme',
                'template_assets',
                'form_factory',
                'twig',
            )
        );

        $resolver->setAllowedTypes(
            array(
                'configuration_handler' => '\RedKiteCms\Configuration\ConfigurationHandler',
                'pages_collection_parser' => '\RedKiteCms\Content\PageCollection\PagesCollectionParser',
                'theme' => '\RedKiteCms\Content\Theme\Theme',
                'template_assets' => '\RedKiteCms\Rendering\TemplateAssetsManager\TemplateAssetsManager',
                'form_factory' => '\Symfony\Component\Form\FormFactory',
                'twig' => '\Twig_Environment',
            )
        );
    }
}