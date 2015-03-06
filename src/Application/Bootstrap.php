<?php

namespace Martial\Warez\Application;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use GuzzleHttp\Client as GuzzleClient;
use Martial\Warez\Front\Controller\HomeController;
use Martial\Warez\Front\Controller\SecurityController;
use Martial\Warez\Front\Controller\UserController;
use Martial\Warez\Security\AuthenticationProvider;
use Martial\Warez\T411\Api\Client;
use Martial\Warez\T411\Api\Data\DataTransformer;
use Martial\Warez\User\UserService;
use Silex\Application;
use Silex\Provider\DoctrineServiceProvider;
use Silex\Provider\FormServiceProvider;
use Silex\Provider\MonologServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\TranslationServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\ValidatorServiceProvider;

class Bootstrap
{
    /**
     * @var Application
     */
    private $app;

    /**
     * @var array
     */
    private $config;

    /**
     * @var string
     */
    private $env;

    /**
     * @param Application $app
     * @param array $config
     * @param string $env
     */
    public function __construct(Application $app, array $config, $env = 'prod')
    {
        $this->app = $app;
        $this->config = $config;
        $this->env = $env;
        $this->registerServiceProviders();
        $this->configureApplication();
        $this->registerServices();
    }

    /**
     * @param Application $app
     * @param array $config
     * @param string $env
     * @return Bootstrap
     */
    public static function createApplication(Application $app, array $config, $env = 'prod')
    {
        return new self($app, $config, $env);
    }

    protected function configureApplication()
    {
        $this->app['env'] = $this->env;
        $this->app['debug'] = $this->env == 'dev';

        foreach ($this->config['twig']['paths'] as $namespace => $paths) {
            $this->app['twig.loader.filesystem']->setPaths($paths, $namespace);
        }
    }

    protected function registerServiceProviders()
    {
        $this->app
            ->register(new ServiceControllerServiceProvider())
            ->register(new TwigServiceProvider(), $this->config['twig'])
            ->register(new MonologServiceProvider(), $this->config['monolog'])
            ->register(new SessionServiceProvider())
            ->register(new FormServiceProvider())
            ->register(new ValidatorServiceProvider())
            ->register(new TranslationServiceProvider(), $this->config['translator'])
            ->register(new UrlGeneratorServiceProvider())
            ->register(new DoctrineServiceProvider(), $this->config['doctrine']['dbal']);
    }

    protected function registerServices()
    {
        $app = $this->app;

        $app['doctrine.entity_manager'] = $app->share(function() {

            if ('dev' == $this->env) {
                $cache = new ArrayCache();
            } else {
                $cache = new FilesystemCache($this->config['doctrine']['orm']['cache_dir']);
            }

            $config = Setup::createAnnotationMetadataConfiguration(
                $this->config['doctrine']['orm']['paths'],
                $this->app['debug'],
                $this->config['doctrine']['orm']['proxy_dir'],
                $cache
            );

            return EntityManager::create($this->config['doctrine']['dbal']['db.options'], $config);
        });
        
        $app['t411.api.http_client'] = $app->share(function() {
            return new GuzzleClient([
                'base_url' => 'https://api.t411.me'
            ]);
        });

        $app['t411.api.data.data_transformer'] = $app->share(function() {
            return new DataTransformer();
        });

        $app['t411.api.client'] = $app->share(function() use ($app) {
            return new Client(
                $app['t411.api.http_client'],
                $app['t411.api.data.data_transformer']
            );
        });

        $app['authentication_provider'] = $app->share(function() use ($app) {
            return new AuthenticationProvider($app['doctrine.entity_manager']);
        });

        $app['user.service'] = $app->share(function() use ($app) {
            return new UserService($app['doctrine.entity_manager'], $app['authentication_provider']);
        });

        $app['home.controller'] = $app->share(function() use ($app) {
            return new HomeController(
                $app['twig'],
                $app['form.factory'],
                $app['session'],
                $app['url_generator']
            );
        });

        $app['user.controller'] = $app->share(function() use ($app) {
            return new UserController(
                $app['twig'],
                $app['form.factory'],
                $app['session'],
                $app['url_generator'],
                $app['user.service']
            );
        });

        $app['security.controller'] = $app->share(function() use ($app) {
            return new SecurityController(
                $app['twig'],
                $app['form.factory'],
                $app['session'],
                $app['url_generator']
            );
        });
    }
}
