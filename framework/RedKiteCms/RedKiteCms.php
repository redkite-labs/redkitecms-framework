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

namespace RedKiteCms;

use Assetic\Filter\CssRewriteFilter;
use Doctrine\Common\Annotations\AnnotationRegistry;
use JMS\Serializer\SerializerBuilder;
use Monolog\Logger;
use RedKiteCms\Action\FactoryAction;
use RedKiteCms\Bridge\Assetic\AsseticFactoryBuilder;
use RedKiteCms\Bridge\Dispatcher\Dispatcher;
use RedKiteCms\Bridge\ElFinder\ElFinderFilesConnector;
use RedKiteCms\Bridge\ElFinder\ElFinderMediaConnector;
use RedKiteCms\Bridge\Form\FormFactory;
use RedKiteCms\Bridge\Monolog\DataLogger;
use RedKiteCms\Bridge\Routing\RoutingGenerator;
use RedKiteCms\Bridge\Security\UserProvider;
use RedKiteCms\Bridge\Translation\TranslationLoader;
use RedKiteCms\Bridge\Translation\Translator;
use RedKiteCms\Configuration\ConfigurationHandler;
use RedKiteCms\Configuration\SiteBuilder;
use RedKiteCms\Content\Block\BlockFactory;
use RedKiteCms\Content\BlockManager\BlockManager;
use RedKiteCms\Content\BlockManager\BlockManagerApprover;
use RedKiteCms\Content\BlockManager\BlockManagerFactory;
use RedKiteCms\Content\PageCollection\PageCollectionManager;
use RedKiteCms\Content\PageCollection\PagesCollectionParser;
use RedKiteCms\Content\PageCollection\PermalinkManager;
use RedKiteCms\Content\Page\PageManager;
use RedKiteCms\Content\SitemapGenerator\SitemapGenerator;
use RedKiteCms\Content\SlotsManager\SlotsManagerFactory;
use RedKiteCms\Content\Theme\ThemeSlotsGenerator;
use RedKiteCms\Content\Theme\Theme;
use RedKiteCms\Content\Theme\ThemeAligner;
use RedKiteCms\Content\Theme\ThemeDeployer;
use RedKiteCms\Content\Theme\ThemeGenerator;
use RedKiteCms\EventSystem\CmsEvents;
use RedKiteCms\EventSystem\Event\Cms\CmsBootedEvent;
use RedKiteCms\EventSystem\Event\Cms\CmsBootingEvent;
use RedKiteCms\EventSystem\Listener\Block\BlockEditingListener;
use RedKiteCms\EventSystem\Listener\Cms\CmsBootingListener;
use RedKiteCms\EventSystem\Listener\Exception\ExceptionListener;
use RedKiteCms\EventSystem\Listener\PageCollection\PageRemovedListener;
use RedKiteCms\EventSystem\Listener\PageCollection\PageSavedListener;
use RedKiteCms\EventSystem\Listener\PageCollection\TemplateChangedListener;
use RedKiteCms\EventSystem\Listener\Page\PermalinkChangedListener;
use RedKiteCms\EventSystem\Listener\Request\QueueListener;
use RedKiteCms\EventSystem\Listener\Request\ThemeAlignerListener;
use RedKiteCms\FilesystemEntity\Page;
use RedKiteCms\FilesystemEntity\SlotParser;
use RedKiteCms\Flint\ChainMatcher;
use RedKiteCms\Flint\ChainUrlGenerator;
use RedKiteCms\Plugin\PluginManager;
use RedKiteCms\Rendering\PageRenderer\PageRendererBackend;
use RedKiteCms\Rendering\PageRenderer\PageRendererProduction;
use RedKiteCms\Rendering\Queue\QueueManager;
use RedKiteCms\Rendering\TemplateAssetsManager\TemplateAssetsManager;
use RedKiteCms\Rendering\Toolbar\ToolbarManager;
use Silex\Application;
use Silex\Provider\FormServiceProvider;
use Silex\Provider\MonologServiceProvider;
use Silex\Provider\SecurityServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\TranslationServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\OptionsResolver\OptionsResolver;
use RedKiteCms\Bridge\Routing\Routing;

/**
 * Class RedKiteCms is the object deputed to bootstrap the CMS. This object is the application entry point and it is
 * used in front-controllers.
 *
 * @author  RedKite Labs <webmaster@redkite-labs.com>
 * @package RedKiteCms
 */
abstract class RedKiteCms
{
    private $app;
    private $siteName;
    private $frameworkAbsoluteDir;

    /**
     * Returns an array of options to change RedKite CMS configuration
     * @return array
     */
    abstract protected function configure();

    /**
     * Registers additional services
     *
     * @param Application $app
     */
    abstract protected function register(Application $app);

    /**
     * Constructor
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Bootstraps the application
     *
     * @param string $rootDir
     * @param string $siteName
     */
    public function bootstrap($rootDir, $siteName)
    {
        $this->app["red_kite_cms.root_dir"] = $rootDir;
        $this->siteName = $siteName;

        $this->checkPermissions($rootDir);
        $this->initCmsRequiredServices();
        $this->registerProviders();
        $this->registerServices();
        $this->registerListeners();
        $this->register($this->app);
        $this->boot();
        $this->addWebsiteRoutes();

        $this->app["dispatcher"]->dispatch(
            CmsEvents::CMS_BOOTED,
            new CmsBootedEvent($this->app["red_kite_cms.configuration_handler"])
        );
    }

    private function initCmsRequiredServices()
    {
        $configurationOptions = $this->configure();
        $this->app["red_kite_cms.configuration_handler"] = new ConfigurationHandler(
            $this->app["red_kite_cms.root_dir"],
            $this->siteName
        );
        $this->app["red_kite_cms.configuration_handler"]->setConfigurationOptions($configurationOptions);
        $siteNameDir = $this->app["red_kite_cms.root_dir"] . '/app/data/' . $this->siteName;
        if (!is_dir($siteNameDir)) {
            $siteBuilder = new SiteBuilder($this->app["red_kite_cms.root_dir"], $this->siteName);
            $siteBuilder->build();
        }

        $this->app["red_kite_cms.configuration_handler"]->boot();
        $this->frameworkAbsoluteDir = $this->app["red_kite_cms.configuration_handler"]->frameworkAbsoluteDir();
    }

    private function registerTwig()
    {
        $this->app->register(
            new TwigServiceProvider(),
            array(
                'twig.path' => array(
                    $this->app["red_kite_cms.root_dir"] . '/' . $this->frameworkAbsoluteDir . '/plugins/RedKiteCms/Core',
                    $this->app["red_kite_cms.root_dir"] . '/' . $this->frameworkAbsoluteDir . '/plugins/RedKiteCms/Block',
                    $this->app["red_kite_cms.root_dir"] . '/' . $this->frameworkAbsoluteDir . '/plugins/RedKiteCms/Theme',
                    $this->app["red_kite_cms.root_dir"] . '/app/plugins/RedKiteCms/Block',
                    $this->app["red_kite_cms.root_dir"] . '/app/plugins/RedKiteCms/Theme',
                    $this->app["red_kite_cms.root_dir"] . '/src',
                ),
                'twig.options' => array(
                    'cache' => $this->app["red_kite_cms.configuration_handler"]->siteCacheDir() . '/twig',
                ),
            )
        );

        $this->app['twig'] = $this->app->share(
            $this->app->extend(
                'twig',
                function ($twig, $app) {
                    $twig->addGlobal('cms_language', 'en');

                    return $twig;
                }
            )
        );
    }

    private function registerProviders()
    {
        AnnotationRegistry::registerAutoloadNamespace(
            'JMS\Serializer\Annotation',
            $this->app["red_kite_cms.root_dir"] . '/vendor/jms/serializer/src'
        );

        $app = $this->app;
        $siteName = $this->siteName;
        $this->app['security.firewalls'] = array(
            'backend' => array(
                'pattern' => '^/backend',
                'form' => array(
                    'login_path' => '/login',
                    'check_path' => '/backend/login_check',
                ),
                'logout' => array(
                    'logout_path' => '/backend/logout',
                    'target_url' => '/login',
                ),
                'users' => $this->app->share(
                    function () use ($app, $siteName) {
                        return new UserProvider($app["red_kite_cms.root_dir"], $siteName);
                    }
                ),
            ),
        );

        // TODO setup roles dinamically
        $app['security.access_rules'] = array(
            array('^.*$', 'ROLE_USER'),
        );

        $this->app->register(new UrlGeneratorServiceProvider());
        $this->app->register(new SecurityServiceProvider());
        $this->app->register(new SessionServiceProvider());

        $this->app->boot();

        $this->app->register(
            new TranslationServiceProvider(),
            array(
                'locale_fallbacks' => array('en'),
            )
        );
        $this->app->register(new FormServiceProvider());

        $this->registerTwig();

        $frameworkAbsoluteDir = $this->frameworkAbsoluteDir;
        $this->app['translator'] = $this->app->share(
            $this->app->extend(
                'translator',
                function ($translator, $app) use($frameworkAbsoluteDir) {
                    $resources = array(
                        $app["red_kite_cms.root_dir"] . '/' . $frameworkAbsoluteDir . '/plugins/RedKiteCms/Core/RedKiteCms/Resources/translations',
                        $app["red_kite_cms.root_dir"] . '/' . $frameworkAbsoluteDir . '/plugins/RedKiteCms/Block/*/Resources/translations',
                    );

                    // This is a workaround required because Symfony2 Finder throws an exception when a folder does not exist, so the
                    // path for custom bundles cannot be added arbitrarily.
                    // This code looks for at least once "translations" folder for custom plugins and when it finds one, it adds the
                    // path to find translations for custom plugins
                    foreach ($app["red_kite_cms.plugin_manager"]->getBlockPlugins() as $plugin) {
                        if ($plugin->isCore()) {
                            continue;
                        }

                        if ($plugin->isTranslated()) {
                            $resources[] = $app["red_kite_cms.root_dir"] . '/app/plugins/RedKiteCms/Block/*/Resources/translations';

                            break;
                        }
                    }

                    $translationLoader = new TranslationLoader();
                    $translationLoader->registerResources($translator, $resources);
                    $translator->addLoader('xliff', $translationLoader);

                    return $translator;
                }
            )
        );

        $logFileName = $this->siteName;
        if ($this->app["debug"]){
            $logFileName .= '_dev';
        }
        $logPath = sprintf('%s/%s.log', $this->app["red_kite_cms.configuration_handler"]->logDir(), $logFileName);
        $level = $this->app["debug"] ? Logger::DEBUG : Logger::CRITICAL;
        $app->register(
            new MonologServiceProvider(),
            array(
                'monolog.name' => 'RedKiteCms',
                'monolog.logfile' => $logPath,
                'monolog.level' => $level,
            )
        );

        $app['router'] = Routing::create($this->app["red_kite_cms.configuration_handler"], $this->app["debug"])->getRouter();

        $generator = new ChainUrlGenerator(array($this->app['url_generator'], $this->app['router']));
        $generator->setContext($this->app['request_context']);
        $this->app['url_generator'] = $generator;
    }

    private function registerServices()
    {
        $optionsResolver = new OptionsResolver();

        $this->app["jms.serializer"] = SerializerBuilder::create()->build();
        $this->app["red_kite_cms.plugin_manager"] = new PluginManager($this->app["red_kite_cms.configuration_handler"]);
        $this->app["red_kite_cms.slot_parser"] = new SlotParser($this->app["jms.serializer"]);
        $this->app["red_kite_cms.page"] = new Page(
            $this->app["jms.serializer"],
            clone $optionsResolver,
            $this->app["red_kite_cms.slot_parser"]
        );
        $this->app["red_kite_cms.block_factory"] = new BlockFactory($this->app["red_kite_cms.configuration_handler"]);

        $this->app["red_kite_cms.blocks_manager_factory"] = new BlockManagerFactory(
            $this->app["jms.serializer"],
            $this->app["red_kite_cms.block_factory"],
            clone $optionsResolver
        );
        $this->app["red_kite_cms.pages_collection_parser"] = new PagesCollectionParser($this->app["red_kite_cms.configuration_handler"]);
        $this->app["red_kite_cms.form_factory"] = new FormFactory(
            $this->app["red_kite_cms.configuration_handler"],
            $this->app["form.factory"],
            $this->app["red_kite_cms.pages_collection_parser"]
        );
        $this->app["red_kite_cms.assetic"] = new AsseticFactoryBuilder(
            $this->app["red_kite_cms.configuration_handler"]
        );
        $this->app["red_kite_cms.template_assets"] = new TemplateAssetsManager(
            $this->app["red_kite_cms.configuration_handler"], $this->app["red_kite_cms.assetic"]
        );
        $this->app["red_kite_cms.page_renderer_backend"] = new PageRendererBackend(
            $this->app["twig"],
            $this->app["red_kite_cms.pages_collection_parser"]
        );
        $this->app["red_kite_cms.page_renderer_production"] = new PageRendererProduction(
            $this->app["red_kite_cms.configuration_handler"], $this->app["jms.serializer"], $this->app["twig"]
        );
        $this->app["red_kite_cms.block_manager"] = new BlockManager(
            $this->app["jms.serializer"],
            $this->app["red_kite_cms.block_factory"],
            clone $optionsResolver
        );
        $this->app["red_kite_cms.slots_manager_factory"] = new SlotsManagerFactory(
            $this->app["red_kite_cms.configuration_handler"]
        );
        $this->app["red_kite_cms.page_collection_manager"] = new PageCollectionManager(
            $this->app["red_kite_cms.configuration_handler"],
            $this->app["red_kite_cms.slots_manager_factory"],
            $this->app["dispatcher"]
        );
        $this->app["red_kite_cms.page_manager"] = new PageManager(
            $this->app["red_kite_cms.configuration_handler"],
            $this->app["dispatcher"]
        );
        $this->app["red_kite_cms.elfinder_media_connector"] = new ElFinderMediaConnector(
            $this->app["red_kite_cms.configuration_handler"]
        );
        $this->app["red_kite_cms.elfinder_files_connector"] = new ElFinderFilesConnector(
            $this->app["red_kite_cms.configuration_handler"]
        );
        $this->app["red_kite_cms.permalink_manager"] = new PermalinkManager(
            $this->app["red_kite_cms.configuration_handler"]
        );
        $this->app["red_kite_cms.theme"] = new Theme(
            $this->app["red_kite_cms.configuration_handler"],
            $this->app["red_kite_cms.slots_manager_factory"]
        );
        $this->app["red_kite_cms.toolbar_manager"] = new ToolbarManager(
            $this->app["red_kite_cms.plugin_manager"],
            $this->app["twig"]
        );
        $this->app["red_kite_cms.theme_generator"] = new ThemeGenerator($this->app["red_kite_cms.configuration_handler"]);
        $this->app["red_kite_cms.slots_generator"] = new ThemeSlotsGenerator($this->app["red_kite_cms.configuration_handler"], $this->app["red_kite_cms.slots_manager_factory"]);
        $this->app["red_kite_cms.theme_aligner"] = new ThemeAligner($this->app["red_kite_cms.configuration_handler"]);
        $this->app["red_kite_cms.theme_deployer"] = new ThemeDeployer($this->app["red_kite_cms.configuration_handler"]);
        $this->app["red_kite_cms.factory_action"] = new FactoryAction($this->app);
        $this->app["red_kite_cms.queue_manager"] = new QueueManager($this->app["red_kite_cms.configuration_handler"], $this->app["red_kite_cms.factory_action"], $this->app["twig"]);

        $this->app["red_kite_cms.sitemap_generator"] = new SitemapGenerator($this->app["red_kite_cms.configuration_handler"],$this->app["red_kite_cms.pages_collection_parser"],$this->app["twig"]);
    }

    private function checkPermissions($rootDir)
    {
        $permissions = array();
        if (!is_writable($rootDir . '/app')) {
            $permissions[] = $rootDir . '/app';
        }
        if (!is_writable($rootDir . '/app/data')) {
            $permissions[] = $rootDir . '/app/data';
        }
        $webDir = str_replace('/..', '', $rootDir);
        if (!is_writable($webDir)) {
            $permissions[] = $webDir;
        }

        if (!empty($permissions)) {
            $this->registerTwig();
            echo $this->app["twig"]->render('RedKiteCms/Resources/views/Permissions/permissions.html.twig', array("permissions" => $permissions));
            exit;
        }
    }

    private function registerListeners()
    {
        $this->app["red_kite_cms.listener.exception"] = new ExceptionListener(
            $this->app["twig"],
            $this->app["translator"],
            $this->app["debug"]
        );
        $this->app["dispatcher"]->addListener(
            'kernel.exception',
            array($this->app["red_kite_cms.listener.exception"], 'onKernelException')
        );

        $this->app["dispatcher"]->addListener(
            'kernel.request',
            array(new QueueListener($this->app["red_kite_cms.queue_manager"], $this->app["security"]), 'onKernelRequest')
        );

        $this->app["dispatcher"]->addListener(
            'kernel.request',
            array(new ThemeAlignerListener($this->app["red_kite_cms.configuration_handler"], $this->app["red_kite_cms.pages_collection_parser"], $this->app["security"], $this->app["red_kite_cms.theme_generator"], $this->app["red_kite_cms.slots_generator"], $this->app["red_kite_cms.theme_aligner"], clone($this->app["red_kite_cms.page"])), 'onKernelRequest')
        );

        $this->app["red_kite_cms.listener.cms_booting"] = new CmsBootingListener(
            $this->app["red_kite_cms.plugin_manager"]
        );
        $this->app["dispatcher"]->addListener(
            'cms.booting',
            array($this->app["red_kite_cms.listener.cms_booting"], 'onCmsBooting')
        );
        $this->app["red_kite_cms.listener.block_edited"] = new BlockEditingListener(
            $this->app["red_kite_cms.page_renderer_production"], $this->app["red_kite_cms.permalink_manager"]
        );
        $this->app["dispatcher"]->addListener(
            'block.editing',
            array($this->app["red_kite_cms.listener.block_edited"], 'onBlockEditing')
        );
        $this->app["red_kite_cms.listener.permalink_changed"] = new PermalinkChangedListener(
            $this->app["red_kite_cms.configuration_handler"], $this->app["red_kite_cms.permalink_manager"]
        );
        $this->app["dispatcher"]->addListener(
            'page.permalink_changed',
            array($this->app["red_kite_cms.listener.permalink_changed"], 'onPermalinkChanged')
        );
        $this->app["red_kite_cms.listener.page_removed"] = new PageRemovedListener(
            $this->app["red_kite_cms.pages_collection_parser"], $this->app["red_kite_cms.permalink_manager"]
        );
        $this->app["dispatcher"]->addListener(
            'page.collection.removed',
            array($this->app["red_kite_cms.listener.page_removed"], 'onPageRemoved')
        );
        $this->app["red_kite_cms.listener.template_changed"] = new TemplateChangedListener(
            $this->app["red_kite_cms.theme"], $this->app["red_kite_cms.configuration_handler"]
        );
        $this->app["dispatcher"]->addListener(
            'page.collection.template_changed',
            array($this->app["red_kite_cms.listener.template_changed"], 'onTemplateChanged')
        );
        $this->app["red_kite_cms.listener.page_saved"] = new PageSavedListener(
            $this->app["red_kite_cms.configuration_handler"], $this->app["red_kite_cms.page_renderer_production"]
        );
        $this->app["dispatcher"]->addListener(
            'page.saved',
            array($this->app["red_kite_cms.listener.page_saved"], 'onPageSaved')
        );
    }

    private function boot()
    {
        Dispatcher::setDispatcher($this->app["dispatcher"]);
        DataLogger::init($this->app["monolog"]);
        Translator::setTranslator($this->app["translator"]);

        $this->app["red_kite_cms.plugin_manager"]->boot();
        $theme = $this->app["red_kite_cms.plugin_manager"]->getActiveTheme();
        $this->app["red_kite_cms.theme"]->boot($theme);

        $this->app["red_kite_cms.theme_generator"]->boot($theme);
        $this->app["red_kite_cms.slots_generator"]->boot($theme);
        $this->app["red_kite_cms.theme_aligner"]->boot($theme);

        $siteIncompleteFile = $this->app["red_kite_cms.root_dir"] . '/app/data/' . $this->siteName . '/incomplete.json';
        if (file_exists($siteIncompleteFile)) {
            $this->createWebsitePages($theme);
            unlink($siteIncompleteFile);
        }

        $this->app["dispatcher"]->dispatch(
            CmsEvents::CMS_BOOTING,
            new CmsBootingEvent($this->app["red_kite_cms.configuration_handler"])
        );
        $this->app["red_kite_cms.block_factory"]->boot();
        $this->app["red_kite_cms.template_assets"]->boot();
        $this->app["red_kite_cms.assetic"]->addFilter('cssrewrite', new CssRewriteFilter());
    }

    private function addWebsiteRoutes()
    {
        // FIXME This information comes from security and it is not available at this level
        $user = null;
        if (!$this->app["red_kite_cms.configuration_handler"]->isTheme()) {
            $user = 'admin';
        }
        $routingGenerator = new RoutingGenerator($this->app["red_kite_cms.configuration_handler"]);
        Routing::create($this->app["red_kite_cms.configuration_handler"])->generateWebsiteRoutes($routingGenerator, $user);
        $this->app["red_kite_cms.website_routes"] = $routingGenerator->getRoutes();

        $matcher = new ChainMatcher(array($this->app['url_matcher'], $this->app['router']->getMatcher()));
        $matcher->setContext($this->app['request_context']);
        $this->app['url_matcher'] = $matcher;
    }

    private function createWebsitePages($theme)
    {
        $isTheme = $this->app["red_kite_cms.configuration_handler"]->isTheme();
        $user = null;
        if (!$isTheme) {
            $user = 'admin';
        }

        $language = "en_GB";
        $pages = $theme->getPages();
        if (null == $pages) {
            $this->registerTwig();
            echo $this->app["twig"]->render('RedKiteCms/Resources/views/Bootstrap/bootstrap.html.twig', array("theme_name" => $theme->getName(), "site_path" => $this->app["red_kite_cms.configuration_handler"]->siteDir()));
            exit;
        }
        $this->app["red_kite_cms.page_collection_manager"]->contributor($user);

        $theme = $this->app["red_kite_cms.theme"];
        $this->app["red_kite_cms.slots_generator"]->generate();
        foreach($pages as $pageName => $templateName) {
            $page = array(
                "name" => $pageName,
                "template" => $templateName,
                "seo" => array(
                    array(
                        "permalink" => str_replace('_', '-', strtolower($language)) . "-" . $pageName,
                        "changed_permalinks" => array(),
                        "title" => $pageName . '-title',
                        "description" => $pageName . '-description',
                        "keywords" => $pageName . '-keywords',
                        "sitemap_frequency" => 'monthly',
                        "sitemap_priority" => '0.5',
                        "language" => $language,
                    ),)
            );
            $this->app["red_kite_cms.page_collection_manager"]
                ->add($theme, $page)
            ;

            $page = new Page($this->app["jms.serializer"],
                new OptionsResolver(),
                $this->app["red_kite_cms.slot_parser"]
            );
            $pageOptions = array(
                'page' => $pageName,
                'language' => 'en',
                'country' => 'GB',
            );
            $page->render($this->app["red_kite_cms.configuration_handler"]->siteDir(), $pageOptions, $user);
            $this->savePermalinks($page->getPageSlots());
            $this->savePermalinks($page->getCommonSlots());
            $this->app["red_kite_cms.permalink_manager"]->save();
        }

        if (!$isTheme) {
            $blockManager = new BlockManagerApprover(
                $this->app["jms.serializer"],
                $this->app["red_kite_cms.block_factory"],
                new OptionsResolver()
            );
            $this->app["red_kite_cms.page_collection_manager"]->saveAllPages($blockManager, array('en_GB'));
        }

        $fileSystem = new Filesystem();
        $fileSystem->remove($this->app["red_kite_cms.configuration_handler"]->siteCacheDir());
    }

    private function savePermalinks($slots)
    {
        foreach($slots as $slot) {
            foreach($slot->getEntitiesInUse() as $block) {
                $slotDir = $slot->getDirInUse();
                $decodedBlock = json_decode($block, true);
                $blockFile = $slotDir . '/blocks/' . $decodedBlock["name"] . '.json';
                $htmlBlock = $this->app["red_kite_cms.page_renderer_production"]->renderBlock($block);
                $this->app["red_kite_cms.permalink_manager"]->add($blockFile, $htmlBlock);
            }
        }
    }
}