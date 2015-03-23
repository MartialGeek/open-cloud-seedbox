<?php

namespace Martial\Warez\Application;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use GuzzleHttp\Client as GuzzleClient;
use Martial\Warez\Front\Controller\AbstractController;
use Martial\Warez\Security\AuthenticationProvider;
use Martial\Warez\Security\BlowfishHashPassword;
use Martial\Warez\Security\Firewall;
use Martial\Warez\Security\OpenSSLEncoder;
use Martial\Warez\T411\Api\Client;
use Martial\Warez\T411\Api\Data\DataTransformer;
use Martial\Warez\T411\Api\Search\QueryFactory;
use Martial\Warez\Upload\UploadAdapterFactory;
use Martial\Warez\Upload\UploadUrlResolver;
use Martial\Warez\User\ProfileService;
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
use Symfony\Component\Filesystem\Filesystem;

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
        $this->registerServices();
        $this->configureApplication();
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

    public function registerControllers(array $controllers)
    {
        $app = $this->app;

        foreach ($controllers as $serviceKey => $definition) {
            $app[$serviceKey] = $app->share(function() use ($app, $definition) {
                $dependencies = isset($definition['dependencies']) ? $definition['dependencies'] : [];

                return $this->getControllerInstance($definition['class'], $dependencies);
            });
        }
    }

    protected function configureApplication()
    {
        $this->app['env'] = $this->env;
        $this->app['debug'] = $this->env == 'dev';

        foreach ($this->config['twig']['paths'] as $namespace => $paths) {
            $this->app['twig.loader.filesystem']->setPaths($paths, $namespace);
        }

        $this->app['dispatcher']->addListener('kernel.request', [
            $this->app['security.firewall'],
            'onKernelRequest'
        ]);
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
        $config = $this->config;

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

        $app['filesystem'] = $app->share(function() {
            return new Filesystem();
        });
        
        $app['t411.api.http_client'] = $app->share(function() use ($config) {
            return new GuzzleClient([
                'base_url' => $config['tracker']['base_url']
            ]);
        });

        $app['t411.api.data.data_transformer'] = $app->share(function() {
            return new DataTransformer();
        });

        $app['t411.api.query_factory'] = $app->share(function() {
            return new QueryFactory();
        });

        $app['t411.api.client'] = $app->share(function() use ($app, $config) {
            return new Client(
                $app['t411.api.http_client'],
                $app['t411.api.data.data_transformer'],
                $app['filesystem'],
                $app['t411.api.query_factory'],
                $config['tracker']['client']
            );
        });

        $app['security.password_hash'] = $app->share(function() {
            return new BlowfishHashPassword();
        });

        $app['security.authentication_provider'] = $app->share(function() use ($app) {
            return new AuthenticationProvider($app['security.password_hash']);
        });

        $app['security.encoder'] = $app->share(function() use ($config) {
            return new OpenSSLEncoder(
                $config['security']['encoder']['password'],
                $config['security']['encoder']['salt']
            );
        });

        $app['user.service'] = $app->share(function() use ($app) {
            return new UserService(
                $app['doctrine.entity_manager'],
                $app['security.authentication_provider'],
                $app['security.password_hash'],
                $app['profile.service']
            );
        });

        $app['profile.service'] = $app->share(function() use ($app) {
            return new ProfileService($app['security.encoder']);
        });

        $app['security.firewall'] = $app->share(function() use ($app) {
            return new Firewall($app['session']);
        });

        $app['upload.http_client'] = $app->share(function() use ($app, $config) {
            return new GuzzleClient([
                'base_url' => $config['upload']['transport']['host'] . ':' . $config['upload']['transport']['port']
            ]);
        });

        $app['upload.url_resolver'] = $app->share(function() {
            return new UploadUrlResolver();
        });

        $app['upload.adapter_factory'] = $app->share(function() use ($app, $config) {
            return new UploadAdapterFactory($app['upload.http_client'], $app['upload.url_resolver']);
        });

        $app['upload.freebox_adapter'] = $app->share(function() use ($app, $config) {
            return $app['upload.adapter_factory']->get(
                $config['upload']['adapter'],
                $config['upload']['adapter_config']
            );
        });
    }

    /**
     * Returns an instance of a controller class.
     *
     * @param string $controllerClass
     * @param array $additionalDependencies
     * @return AbstractController
     */
    protected function getControllerInstance($controllerClass, array $additionalDependencies = [])
    {
        $reflectionClass = new \ReflectionClass($controllerClass);

        $defaultDependencies = [
            $this->app['twig'],
            $this->app['form.factory'],
            $this->app['session'],
            $this->app['url_generator']
        ];

        return $reflectionClass->newInstanceArgs(array_merge($defaultDependencies, $additionalDependencies));
    }
}
